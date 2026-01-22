export class PrideService {
    public static readonly PRIDE_CLASSES = [
        'BiPlus',
        'Trans',
        'NonBinary',
        'Asexual',
        'Pansexual',
        'GayMale',
        'Lesbian',
        'Intersex',
        'GenderFluid',
        'Agender',
        ' Polyamorous',
        'Omnisexual',
        'Polysexual',
        'AroAce',
        'Genderqueer',
        'Queer',
    ];

    public static isPrideMonth(): boolean {
        return new Date().getMonth() + 1 === 6;
    }

    public static getCssClassesForPrideFlag(): string | null {
        // only run in june
        if (!this.isPrideMonth()) {
            return null;
        }
        const rand = Math.floor(Math.random() * 101);

        if (rand < 70) {
            return 'Gay text-pride';
        }

        return this.PRIDE_CLASSES[Math.floor(Math.random() * this.PRIDE_CLASSES.length)] + ' text-pride';
    }
}
