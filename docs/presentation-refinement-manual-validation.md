# Presentation refinement manual validation

Use an interactive browser to verify:

1. Enabled buttons show a pointer cursor.
2. Links and button-like links show a pointer cursor.
3. Disabled controls do not show an enabled pointer state.
4. Static cards, badges, and rows do not look clickable.
5. Official draw modal opens without revealing a team.
6. Rows #1 through #N are immediately present as placeholders.
7. Pick #1 is visually at the top.
8. Pick #N is visually at the bottom.
9. List height remains stable while revealing.
10. Rows never move or reorder.
11. Manual mode is selected by default.
12. Start Lottery enters manual revealing state.
13. Start Lottery does not itself reveal a team.
14. Reveal Pick #N reveals only that row.
15. Each subsequent activation reveals exactly one position.
16. Rapid double clicking does not skip positions.
17. Position #1 is revealed last.
18. Winner is not visible or announced before #1.
19. Winner emphasis appears after #1 reveal.
20. Complete state displays the full ascending order.
21. Replay Presentation resets all rows to placeholders.
22. Replay waits for Start Replay.
23. Manual or Autoplay can be selected again after reset.
24. Autoplay requires explicit selection.
25. Autoplay requires Start Lottery or Start Replay.
26. Autoplay reveals N through #1.
27. Autoplay timing is slow enough to create suspense.
28. Show Final Result works before starting.
29. Skip works in manual mode.
30. Skip cancels timers in autoplay mode.
31. No delayed reveal runs after Skip.
32. Closing the modal clears active timers.
33. Reopening does not resume stale animation.
34. Reduced-motion Manual remains user controlled.
35. Reduced-motion Autoplay still waits for Start.
36. Keyboard-only operation works.
37. Focus remains managed correctly.
38. Mobile layout retains #1 at the top.
39. Long team names do not break the layout.
40. Simulation statistics still increment only once per new server result.
