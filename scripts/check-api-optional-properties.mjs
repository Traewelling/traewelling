#!/usr/bin/env node
/**
 * Checks that all properties in OpenAPI schema components are declared as required.
 *
 * API design rule: fields must always be present in responses. Use nullable: true
 * for fields that can be null — never leave a property out of the required array,
 * which would make it possibly undefined in generated TypeScript clients.
 */

import { readFileSync } from 'fs';
import { resolve } from 'path';

const specPath = resolve(process.cwd(), 'storage/api-docs/api-docs.json');
const spec = JSON.parse(readFileSync(specPath, 'utf-8'));
const schemas = spec?.components?.schemas ?? {};

// Collect named schema names that are used exclusively as request bodies.
// For those, optional properties are intentionally fine (dedicated request DTOs).
const requestOnlySchemas = new Set();
const responseSchemasRefs = new Set();

for (const pathItem of Object.values(spec?.paths ?? {})) {
    for (const operation of Object.values(pathItem)) {
        if (typeof operation !== 'object') continue;
        for (const mediaType of Object.values(operation.requestBody?.content ?? {})) {
            const ref = mediaType?.schema?.$ref;
            if (ref) requestOnlySchemas.add(ref.split('/').pop());
        }
        for (const response of Object.values(operation.responses ?? {})) {
            for (const mediaType of Object.values(response?.content ?? {})) {
                const ref = mediaType?.schema?.$ref;
                if (ref) responseSchemasRefs.add(ref.split('/').pop());
            }
        }
    }
}

// Any named schema referenced from a response is not request-only.
for (const name of responseSchemasRefs) {
    requestOnlySchemas.delete(name);
}

const violations = [];

function checkSchema(schema, context) {
    const props = schema.properties ?? {};
    const required = new Set(schema.required ?? []);
    for (const propName of Object.keys(props)) {
        if (!required.has(propName)) {
            violations.push(`${context}.${propName}`);
        }
    }
}

// Check named component schemas (skip request-only dedicated DTOs).
for (const [schemaName, schema] of Object.entries(schemas)) {
    if (requestOnlySchemas.has(schemaName)) continue;
    checkSchema(schema, schemaName);
}

// Check inline schemas defined directly inside path operations.
// These are never caught by the component-schema loop above.
for (const pathItem of Object.values(spec?.paths ?? {})) {
    for (const operation of Object.values(pathItem)) {
        if (typeof operation !== 'object' || !operation.operationId) continue;

        for (const [statusCode, response] of Object.entries(operation.responses ?? {})) {
            for (const mediaType of Object.values(response?.content ?? {})) {
                const schema = mediaType?.schema;
                if (!schema || schema['$ref']) continue;
                checkSchema(schema, `${operation.operationId} (${statusCode} response)`);
            }
        }
    }
}

if (violations.length > 0) {
    console.error('');
    console.error('❌ API schema violation: optional properties found (can be undefined)');
    console.error('');
    console.error('All properties must be listed in the schema\'s "required" array.');
    console.error('If a field can be absent, use nullable: true and keep it required.');
    console.error('');
    for (const v of violations) {
        console.error(`  - ${v}`);
    }
    console.error('');
    process.exit(1);
}

console.log('✓ All schema properties are required (no possibly-undefined properties)');
