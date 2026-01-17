import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import globals from 'globals';

export default [
    js.configs.recommended,
    ...pluginVue.configs['flat/recommended'],
    {
        languageOptions: {
            globals: {
                ...globals.browser,
                ...globals.node,
                route: 'readonly',
                axios: 'readonly',
            },
        },
        rules: {
            'vue/multi-word-component-names': 'off',
            'no-unused-vars': 'warn',
            'vue/no-unused-vars': 'warn',
            'vue/no-mutating-props': 'warn',
            'no-useless-escape': 'off',
            'vue/no-use-v-if-with-v-for': 'warn',
        },
    },
    {
        ignores: [
            'vendor/**',
            'public/**',
            'node_modules/**',
            'dist/**',
            '.github/**',
        ],
    },
];
