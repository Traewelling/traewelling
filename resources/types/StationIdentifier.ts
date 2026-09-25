export const IDENTIFIER_TYPES = ['motis', 'wikidata_id', 'ifopt', 'de_db_ril100', 'de_db_ibnr', 'local_code'] as const;
export type IdentifierType = (typeof IDENTIFIER_TYPES)[number];
