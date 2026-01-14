import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import tseslint from 'typescript-eslint';

export default [
    // Base recommended configs
    js.configs.recommended,
    ...tseslint.configs.recommended,
    ...pluginVue.configs['flat/recommended'],

    // Global ignores
    {
        ignores: [
            'node_modules/**',
            'vendor/**',
            'public/build/**',
            'public/hot',
            'storage/**',
            'bootstrap/cache/**',
            '.phpstorm.meta.php',
            '_ide_helper.php',
            '_ide_helper_models.php',
        ],
    },

    // Configuration for all files
    {
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                // Browser globals
                window: 'readonly',
                document: 'readonly',
                navigator: 'readonly',
                console: 'readonly',
                // Laravel globals
                route: 'readonly',
                axios: 'readonly',
            },
        },
        rules: {
            // Code quality
            'no-console': ['warn', { allow: ['warn', 'error'] }],
            'no-debugger': 'warn',
            'no-unused-vars': 'off', // Handled by TypeScript
            '@typescript-eslint/no-unused-vars': ['warn', {
                argsIgnorePattern: '^_',
                varsIgnorePattern: '^_',
            }],
            '@typescript-eslint/no-explicit-any': 'warn',

            // Vue specific
            'vue/multi-word-component-names': 'off',
            'vue/require-default-prop': 'off',
            'vue/no-v-html': 'warn',

            // Code style
            'indent': ['error', 4, {
                SwitchCase: 1,
                ignoredNodes: ['TemplateLiteral'],
            }],
            'quotes': ['error', 'single', { avoidEscape: true }],
            'semi': ['error', 'always'],
            'comma-dangle': ['error', 'always-multiline'],
            'eol-last': ['error', 'always'],
            'no-multiple-empty-lines': ['error', { max: 1, maxEOF: 0 }],
            'no-trailing-spaces': 'error',
            'object-curly-spacing': ['error', 'always'],
            'array-bracket-spacing': ['error', 'never'],
            'arrow-spacing': 'error',
            'space-before-blocks': 'error',
            'keyword-spacing': 'error',
        },
    },

    // Vue-specific configuration
    {
        files: ['**/*.vue'],
        languageOptions: {
            parserOptions: {
                parser: tseslint.parser,
                ecmaVersion: 'latest',
                sourceType: 'module',
            },
        },
        rules: {
            'vue/html-indent': ['error', 4],
            'vue/max-attributes-per-line': ['error', {
                singleline: 3,
                multiline: 1,
            }],
            'vue/first-attribute-linebreak': ['error', {
                singleline: 'ignore',
                multiline: 'below',
            }],
            'vue/component-tags-order': ['error', {
                order: ['script', 'template', 'style'],
            }],
            'vue/block-lang': ['error', {
                script: { lang: 'ts' },
            }],
            'vue/component-api-style': ['error', ['script-setup']],
            'vue/define-macros-order': ['error', {
                order: ['defineProps', 'defineEmits', 'defineSlots'],
            }],
        },
    },

    // TypeScript files
    {
        files: ['**/*.ts'],
        languageOptions: {
            parser: tseslint.parser,
            parserOptions: {
                project: './tsconfig.json',
            },
        },
    },
];
