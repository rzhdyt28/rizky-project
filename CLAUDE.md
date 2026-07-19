## Project

Laravel 12 (PHP 8.2) multi-tenant invitation/event platform (`stancl/tenancy`), with a Filament 3.3 admin panel, Midtrans payment integration, Spatie permissions/activitylog, and Pest for tests. Domain code lives under modular namespaces, e.g. `app/Modules/Invitation/` (Models, Http/Controllers, database/migrations, Support), alongside standard `app/Filament/Resources/*` admin resources.

This is the **API/backend** half. The **frontend** is a separate Vue 3 + Vite SPA in the sibling `rizky-project-web` repo (`../rizky-project-web/CLAUDE.md`), which authenticates via Sanctum cookies and consumes this API.

## Token efficiency

- Prefer `graphify query`/`path`/`explain` (see graphify section below) over grepping the whole tree for architecture/relationship questions.
- Query logging to `~/.cache/graphify-queries.log` is on by default; set `GRAPHIFY_QUERY_LOG_DISABLE=1` in the shell env if you don't want query text persisted to disk.
- graphify's code extraction (tree-sitter AST) is free/local — no LLM backend or API key needed unless you explicitly graph non-code files (docs/PDFs/images) with `--backend`.
- Community names in GRAPH_REPORT.md are currently placeholders ("Community N") because no LLM API key is configured. Once one of `ANTHROPIC_API_KEY`/`OPENAI_API_KEY`/`GEMINI_API_KEY`/etc. is set, run `graphify label .` once to name them from the existing graph — no need to re-extract.
- `public/js/filament/`, `public/css/`, and `tools/auto-apply-agent/` are excluded via `.graphifyignore` (vendor-published assets and an unrelated tool) to keep god-node/community signal focused on this project's own code.

## graphify

This project has a knowledge graph at graphify-out/ with god nodes, community structure, and cross-file relationships.

Rules:
- For codebase questions, first run `graphify query "<question>"` when graphify-out/graph.json exists. Use `graphify path "<A>" "<B>"` for relationships and `graphify explain "<concept>"` for focused concepts. These return a scoped subgraph, usually much smaller than GRAPH_REPORT.md or raw grep output.
- If graphify-out/wiki/index.md exists, use it for broad navigation instead of raw source browsing.
- Read graphify-out/GRAPH_REPORT.md only for broad architecture review or when query/path/explain do not surface enough context.
- After modifying code, run `graphify update .` to keep the graph current (AST-only, no API cost).
