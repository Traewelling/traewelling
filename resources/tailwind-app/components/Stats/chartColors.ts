const light = [
    '#c72730', // primary red
    '#3b71ca', // secondary blue
    '#2da44e', // green
    '#e5a823', // amber
    '#8b5cf6', // violet
    '#06b6d4', // cyan
    '#f97316', // orange
    '#ec4899', // pink
];

const dark = [
    '#e84040', // primary red (lighter on dark)
    '#5b91e0', // secondary blue (lighter on dark)
    '#3dd665', // green
    '#f0bc40', // amber
    '#a78bfa', // violet
    '#22d3ee', // cyan
    '#fb923c', // orange
    '#f472b6', // pink
];

export function chartColors(): string[] {
    return document.documentElement.classList.contains('dark') ? dark : light;
}
