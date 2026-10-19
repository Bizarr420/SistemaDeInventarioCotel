# SICAT fixed-asset simulation

## Status and accounting warning

The fixed-asset ledger, depreciation parameters, 2026 movements, and the account-level allocations created by `SicatFixedAssetSimulationSeeder` are a reproducible SICAT simulation. They are not verified balances from the general ledger and must not be represented or posted as official accounting. The 2023 and 2024 published reference totals are reproduced in the simulation to validate report arithmetic; that does not establish official reconciliation.

## Data and relationships

- `fixed_asset_accounts` links each fixed-asset `categories` row to its fixed-asset account and accumulated-depreciation account.
- `fixed_asset_valuations` stores one gross balance per asset and fiscal year, linked to the fixed-asset account.
- `fixed_asset_periods` determines which fiscal years accept ordinary fixed-asset operations. 2023–2025 are closed and 2026 is open by default.
- `fixed_asset_depreciation_parameters` stores each asset's method, effective start, useful life, depreciable base, residual value, source, and profile.
- `fixed_asset_depreciation_parameter_audits` stores before/after parameter snapshots, change type, actor, timestamp, and source for parameter changes made through the 2026 acquisition workflow.
- `fixed_asset_depreciation_entries` stores effective-dated signed depreciation, adjustments, and reversals by asset, account, and year. Depreciation is stored as a negative contra-asset; the Note 12 presentation shows its positive magnitude to match the reference convention.
- `fixed_asset_movements` stores 2026 acquisitions, additions, disposals, revaluations, transfers, and status/location changes with effective dates and source keys.
- `fixed_asset_valuation_originals` and `fixed_asset_profile_originals` preserve the pre-ledger values before the simulation changes.

## Simulation policy

The explicit, editable scenario configuration is in `config/sicat_simulation.php`. Land account `23010301` is non-depreciable in this scenario. Buildings account `24010301` and other assets use straight-line depreciation with asset-specific useful-life variation, a 5% residual assumption, and effective dates generated for test/simulation use. These assumptions require approval and validation before any official accounting use. For new acquisitions, the user supplies the useful life and residual value; the app calculates effective-dated 2026 monthly charges only when a useful life is provided.

Generated depreciation account codes use the simulation prefix `26` followed by the full fixed-asset account code so that similarly suffixed asset accounts do not collide. These generated account codes are scenario identifiers, not an approved chart of accounts.

The seeder distributes 2023 and 2024 Note 11 reference totals across the archived per-asset valuation weights, then distributes Note 12 reference balances across depreciable accounts/assets. Cent allocation is deterministic and account details sum to the published reference totals exactly. 2025 balances are retained from archived source values. The 2026 imported valuation is treated as an opening balance on January 1, 2026; subsequent movements and depreciation are filtered by effective date and report cutoff. The seeder does not infer official balances from current asset ficha fields.

Simulation-generated rows use source identifiers beginning with `SICAT_SIMULATION_V1` or `USER_SIMULATION`. Re-running the seeder regenerates only the scenario's tagged depreciation entries and keeps archived originals, existing valuation rows, and parameters with a non-simulation source. It is intended for the simulation environment, not for production accounting data.

## Operating the open 2026 period

Use **Activos fijos → Alta 2026** or the effective-dated movement controls on the asset detail page. The period must be open and effective dates cannot be in the future. Disposals cannot exceed the asset's gross ledger balance at their effective date; accumulated depreciation is reversed proportionally. The selected report cutoff excludes later effective-dated movements and entries. Editing the asset ficha does not rewrite annual valuations. Assets with ledger history cannot be deleted or moved to a different category; use a dated disposal instead.

## Dashboard indicators

The main dashboard keeps current asset counts separate from the selected financial period. Gross, accumulated depreciation, and net scenario values come from the fixed-asset ledger for the selected year; 2026 accepts a cutoff date from January 1 through today. Status counts reflect the current asset records, not historical status snapshots. Product-stock quantities and values are displayed in a separate inventory summary and are never added to fixed-asset amounts.

The near-end-of-life alert uses a single rule: remaining useful life is at most two years, including assets whose useful life has already expired. Remaining life and consumed percentage use the same acquisition-to-end-of-life date interval; an expired asset reports zero remaining years and 100% consumed. Verification counts distinguish assets without any verification, assets whose latest verification has differences, and assets with a latest verification without differences. The dashboard only reports persisted verifications and activity; it does not synthesize them.

Dashboard charts show the state distribution and the six largest fixed-asset accounts by gross value. Tabs switch the secondary chart between account, department, branch, and age distributions; full records remain available from the filtered asset list and reports.

## Rebuilding and testing the scenario

After applying migrations in a simulation copy of the database, run:

```powershell
php artisan db:seed --class=Database\Seeders\SicatFixedAssetSimulationSeeder --force
```

The operation is idempotent for the tagged simulation dataset. Confirm that Notes 11 and 12 report `Conciliado en simulación` for 2023 and 2024. Review their account detail and the archived-original discrepancy panel before accepting any scenario change.
