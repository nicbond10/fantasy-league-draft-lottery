# Phase 6 manual browser validation

This checklist requires an interactive browser and a clean or known local database.

## Authentication and administration

1. Public registration returns 404.
2. Administrator signs in.
3. Administrator sends invitation.
4. Acceptance URL works.
5. Invitation is accepted once.
6. Revoked invitation fails.
7. Expired invitation fails.
8. Administrator suspends and reactivates a user.
9. Self-suspension is blocked.
10. Final active administrator protection works.

## Lottery configuration

11. User creates an incomplete draft.
12. User adds and removes teams in the intended initial draft order.
13. Local odds total updates.
14. Invalid ready configuration is rejected.
15. Exactly 100.00% becomes ready.
16. Cross-user access is denied.

## Simulation

17. Ready simulation runs.
18. Complete final order appears.
19. Position #1 is the winner.
20. Simulate Again requests a new server result.
21. No official result is persisted.
22. Statistics increment once.
23. Correct team count increments.
24. Observed rates are correct.
25. Closing and reopening the presentation preserves counts.
26. Local replay does not increment counts.
27. Reset clears counts.
28. Refresh resets counts.
29. Original team order remains stable.

## Official draw

30. Ready official lottery shows irreversible confirmation.
31. Official result is generated once.
32. Full-screen modal opens.
33. Reveal does not start automatically.
34. Winner is not visible in the pre-start modal state.
35. Start Lottery begins the reveal.
36. Positions reveal from N through #1.
37. Each position appears once.
38. Position #1 appears last.
39. Complete final order appears after reveal.
40. Completed page persists after refresh.
41. Edit and rerun are unavailable.
42. Replay opens the full-screen modal.
43. Replay waits for Start Replay.
44. Replay shows the identical saved order.
45. Show Final Result works before start.
46. Skip works during reveal.
47. Replay does not affect simulation statistics.

## Accessibility and responsive behaviour

48. Keyboard navigation works.
49. Focus indicators are visible.
50. Confirmation dialog focus works.
51. Full-screen modal focus trapping works.
52. Focus returns after close.
53. Reduced motion waits for Start.
54. Reduced motion reveals promptly after Start.
55. Screen-reader status text is concise.
56. Mobile layout has no serious overflow.
57. Simulation statistics remain understandable on mobile.
58. Archived lotteries remain read-only.
