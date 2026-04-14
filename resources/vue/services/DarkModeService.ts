export type DarkMode = 'light' | 'dark' | 'auto';

class DarkModeService {
    private readonly mediaQuery: MediaQueryList;

    constructor() {
        this.mediaQuery = globalThis.matchMedia('(prefers-color-scheme: dark)');
        this.init();
    }

    private init() {
        this.updateTheme();
        this.mediaQuery.addEventListener('change', () => {
            if (this.getMode() === 'auto') {
                this.updateTheme();
            }
        });
    }

    getMode(): DarkMode {
        const mode = localStorage.getItem('darkMode');
        if (mode === null) {
            localStorage.setItem('darkMode', 'auto');
            return 'auto';
        }
        return mode as DarkMode;
    }

    setMode(mode: DarkMode) {
        localStorage.setItem('darkMode', mode);
        this.updateTheme();
        window.dispatchEvent(new CustomEvent('trwl:darkmode-change'));
    }

    private updateTheme() {
        let setting = this.getMode();
        if (setting === 'auto') {
            setting = this.mediaQuery.matches ? 'dark' : 'light';
        }

        if (setting === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        document.documentElement.dataset.bsTheme = setting;
    }
}

export default new DarkModeService();
