# Phase 5 browser test plan

No JavaScript test framework was added for the MVP. When browser automation is introduced, cover the presentation through the public UI rather than testing Alpine internals.

## Priority scenarios

1. Run an official ready lottery, accept the confirmation, and verify the modal reveals the server-provided positions before leaving the complete order visible.
2. Skip during preparation and during a reveal; verify the complete order appears and no later timer changes it.
3. Close with the button and Escape; verify focus returns to the control that opened the dialog and background interaction is blocked while open.
4. Replay a completed lottery and verify the displayed DTO matches the persisted order without a new draw request.
5. Run a simulation twice through **Simulate Again** and verify each result comes from a separate Livewire response.
6. Emulate `prefers-reduced-motion: reduce` and verify official presentations still wait for Start, manual picks remain user controlled, and autoplay uses shorter delays.
7. Navigate away during a reveal and verify pending timers and global event listeners are cleaned up.
8. Exercise mobile, tablet, desktop, and large-screen viewports with keyboard-only navigation.

Prefer the browser tooling already adopted by the project at that time. Do not add a second framework solely for these scenarios.

## Manual release checklist

1. Dashboard at desktop and mobile widths.
2. Create lottery form.
3. Local odds total.
4. Official ready state.
5. Simulation ready state.
6. Official confirmation.
7. Official presentation.
8. Completed result page.
9. Replay.
10. Simulation and Simulate Again.
11. Skip presentation.
12. Reduced-motion mode.
13. Keyboard-only operation.
14. Archived read-only state.
15. Admin navigation visibility.
