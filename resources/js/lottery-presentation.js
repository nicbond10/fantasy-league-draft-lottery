document.addEventListener('alpine:init', () => {
    window.Alpine.data('lotteryPresentation', () => ({
        state: 'idle',
        result: null,
        source: null,
        revealMode: 'manual',
        configuredRevealMode: 'manual',
        revealedPositions: [],
        nextRevealPosition: null,
        revealLocked: false,
        autoplayDelayMs: 3000,
        revealTransitionMs: 250,
        statusMessage: '',
        statisticsMessage: '',
        timers: [],
        reducedMotion: false,
        eventHandler: null,
        motionQuery: null,
        motionHandler: null,
        simulationCount: 0,
        simulationWins: {},
        countedSimulationIds: [],
        simulationPending: false,

        init() {
            this.motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
            this.reducedMotion = this.motionQuery.matches;
            this.motionHandler = (event) => { this.reducedMotion = event.matches; };
            this.motionQuery.addEventListener?.('change', this.motionHandler);

            this.eventHandler = (event) => this.receive(
                event.detail?.result,
                event.detail?.source,
                event.detail?.presentationMode,
            );
            window.addEventListener('lottery-result-ready', this.eventHandler);
        },

        destroy() {
            this.cleanupTimers();
            window.removeEventListener('lottery-result-ready', this.eventHandler);
            this.motionQuery?.removeEventListener?.('change', this.motionHandler);
        },

        receive(result, source, presentationMode = 'manual') {
            if (!result || !Array.isArray(result.positions)) return;

            this.cleanupTimers();
            this.result = result;
            this.source = source;
            this.configuredRevealMode = ['manual', 'autoplay'].includes(presentationMode)
                ? presentationMode
                : 'manual';
            this.recordSimulation(result, source);
            this.openDialog();

            if (source === 'official' || source === 'replay') {
                this.prepareOfficial();
                return;
            }

            this.completeImmediately('Simulation complete. The complete simulated draft order is available.');
        },

        openDialog() {
            if (!this.$refs.presentationDialog.open) this.$refs.presentationDialog.showModal();
        },

        orderedPositions() {
            return [...(this.result?.positions ?? [])]
                .sort((first, second) => first.final_position - second.final_position);
        },

        revealQueue() {
            return [...this.orderedPositions()].reverse();
        },

        prepareOfficial() {
            this.cleanupTimers();
            this.revealMode = this.configuredRevealMode;
            this.revealedPositions = [];
            this.nextRevealPosition = this.revealQueue()[0]?.final_position ?? null;
            this.revealLocked = false;
            this.state = 'ready';
            this.statusMessage = this.source === 'replay'
                ? 'Official replay ready. Choose a mode and activate Start Replay.'
                : 'Official result saved. Choose a mode and activate Start Lottery.';
            this.$nextTick(() => this.$refs.startButton?.focus());
        },

        startOfficial() {
            if (this.state !== 'ready' || (this.source !== 'official' && this.source !== 'replay')) return;

            this.cleanupTimers();
            this.state = 'revealing';
            this.statusMessage = this.revealMode === 'manual'
                ? `Manual presentation started. Reveal pick ${this.nextRevealPosition}.`
                : 'Autoplay presentation started.';
            this.$nextTick(() => this.$refs.revealButton?.focus());

            if (this.revealMode === 'autoplay') {
                this.scheduleAutoplayReveal();
            }
        },

        revealNextManual() {
            if (this.state !== 'revealing' || this.revealMode !== 'manual' || this.revealLocked) return;

            const position = this.nextPosition();
            if (!position) return;

            this.revealLocked = true;
            this.revealPosition(position);

            if (position.final_position === 1) {
                this.schedule(() => this.finishReveal(), this.transitionDelay());
                return;
            }

            this.schedule(() => {
                this.revealLocked = false;
                this.$nextTick(() => this.$refs.revealButton?.focus());
            }, this.transitionDelay());
        },

        scheduleAutoplayReveal() {
            if (this.state !== 'revealing' || this.revealMode !== 'autoplay') return;

            this.schedule(() => {
                const position = this.nextPosition();
                if (!position) {
                    this.finishReveal();
                    return;
                }

                this.revealPosition(position);

                if (position.final_position === 1) {
                    this.schedule(() => this.finishReveal(), this.transitionDelay());
                    return;
                }

                this.scheduleAutoplayReveal();
            }, this.reducedMotion ? 700 : this.autoplayDelayMs);
        },

        nextPosition() {
            return this.revealQueue()
                .find((position) => !this.revealedPositions.includes(position.final_position));
        },

        revealPosition(position) {
            if (this.revealedPositions.includes(position.final_position)) return;

            this.revealedPositions.push(position.final_position);
            this.nextRevealPosition = this.nextPosition()?.final_position ?? null;
            this.statusMessage = position.final_position === 1
                ? `Pick 1 revealed: ${position.team_name}, the lottery winner.`
                : `Pick ${position.final_position} revealed: ${position.team_name}.`;
        },

        finishReveal() {
            if (this.state === 'complete') return;

            this.cleanupTimers();
            this.revealLocked = false;
            this.nextRevealPosition = null;
            this.state = 'complete';
            this.statusMessage = 'All draft positions have been revealed.';
            this.$nextTick(() => this.$refs.closeButton?.focus());
        },

        isRevealed(finalPosition) {
            return this.revealedPositions.includes(finalPosition);
        },

        revealButtonLabel() {
            return this.nextRevealPosition === null
                ? 'All picks revealed'
                : `Reveal Pick #${this.nextRevealPosition}`;
        },

        transitionDelay() {
            return this.reducedMotion ? 0 : this.revealTransitionMs;
        },

        skip() {
            if (!this.result) return;
            const message = this.state === 'ready'
                ? 'Final result shown without starting the presentation.'
                : 'Presentation skipped. The complete final draft order is available.';
            this.completeImmediately(message);
        },

        completeImmediately(message) {
            this.cleanupTimers();
            this.revealedPositions = this.orderedPositions().map((position) => position.final_position);
            this.nextRevealPosition = null;
            this.revealLocked = false;
            this.state = 'complete';
            this.statusMessage = message;
            this.$nextTick(() => this.$refs.closeButton?.focus());
        },

        replayPresentation() {
            if (!this.result || this.source === 'simulation') return;
            this.source = 'replay';
            this.prepareOfficial();
        },

        async simulateAgain() {
            if (this.simulationPending) return;

            this.cleanupTimers();
            this.simulationPending = true;
            this.statusMessage = 'Generating another simulation result.';

            try {
                await this.$wire.runSimulation();
            } finally {
                this.simulationPending = false;
            }
        },

        recordSimulation(result, source) {
            const eventId = result?.simulation_event_id;
            if (source !== 'simulation' || !eventId || this.countedSimulationIds.includes(eventId)) return;

            const winner = result.positions.find((position) => position.final_position === 1);
            if (!winner || winner.team_id === null || winner.team_id === undefined) return;

            const teamId = String(winner.team_id);
            this.countedSimulationIds.push(eventId);
            this.simulationWins[teamId] = (this.simulationWins[teamId] ?? 0) + 1;
            this.simulationCount += 1;
            this.statisticsMessage = `Simulation complete. ${winner.team_name} won. ${this.simulationCount} ${this.simulationCount === 1 ? 'simulation has' : 'simulations have'} been run this session.`;
        },

        teamWins(teamId) {
            return this.simulationWins[String(teamId)] ?? 0;
        },

        observedRate(teamId) {
            if (this.simulationCount === 0) return '0.00%';
            return `${((this.teamWins(teamId) / this.simulationCount) * 100).toFixed(2)}%`;
        },

        resetSimulationStats() {
            this.simulationCount = 0;
            this.simulationWins = {};
            this.statisticsMessage = 'Temporary simulation statistics reset.';
        },

        openOfficialConfirmation() {
            this.$refs.officialConfirmation.showModal();
            this.$nextTick(() => this.$refs.cancelOfficial?.focus());
        },

        confirmOfficial() {
            this.$refs.officialConfirmation.close();
            this.$wire.runOfficialDraw();
        },

        close() {
            this.cleanupTimers();
            this.revealLocked = false;
            this.state = 'idle';
            this.statusMessage = '';
            if (this.$refs.presentationDialog?.open) this.$refs.presentationDialog.close();
        },

        schedule(callback, delay) {
            const timer = window.setTimeout(callback, delay);
            this.timers.push(timer);
        },

        cleanupTimers() {
            this.timers.forEach((timer) => window.clearTimeout(timer));
            this.timers = [];
        },
    }));
});
