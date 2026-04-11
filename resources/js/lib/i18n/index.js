import { ref } from 'vue';
import { translations } from './translations';

const language = ref(localStorage.getItem('i18n_lang') || 'tj');

export function useI18n() {
    const setLanguage = (lang) => {
        language.value = lang;
        localStorage.setItem('i18n_lang', lang);
    };

    const t = (path) => {
        const keys = path.split('.');
        let current = translations[language.value];
        for (const key of keys) {
            if (current === undefined || current[key] === undefined) return path;
            current = current[key];
        }
        return current;
    };

    return {
        language,
        setLanguage,
        t,
        translations
    };
}
