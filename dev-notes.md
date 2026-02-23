# Marketing Hero Development Notes

## Next steps (post-MVP)

1. **HTMX progressive enhancement**
   - Add HTMX as an optional layer for partial updates on dashboard cards and list sections.
   - Keep server-rendered template partials as the source of truth.

2. **Hosted companion service sync**
   - Implement `SyncClientInterface` with queue + retry strategy.
   - Add explicit opt-in settings and clear data ownership docs.
   - Sync only normalized aggregates first; defer raw event syncing.

3. **Edit flows**
   - Add update handlers and edit forms for activities/results/campaigns.
   - Include validation feedback and sticky form state.

4. **Reporting upgrades**
   - Add campaign ROI calculations and conversion funnel by source.
   - Add downloadable CSV exports.

5. **Performance and observability**
   - Introduce lightweight query profiling in debug mode.
   - Add transient caching for dashboard summaries on high-volume sites.

6. **Quality improvements**
   - Add unit tests for `DateRange` and `DashboardService`.
   - Add integration tests against a WordPress test DB.
