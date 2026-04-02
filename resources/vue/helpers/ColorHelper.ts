export type RGB = {
    red: number;
    green: number;
    blue: number;
};

export function contrastingColor(color: string | RGB) {
    return luma(color) >= 165 ? '000' : 'fff';
}
function luma(color: string | RGB): number {
    const rgb = typeof color === 'string' ? hexToRGBArray(color) : color;
    return 0.2126 * rgb.red + 0.7152 * rgb.green + 0.0722 * rgb.blue;
}

function hexToRGBArray(color: string): RGB {
    if (color.length === 3)
        color =
            color.charAt(0) + color.charAt(0) + color.charAt(1) + color.charAt(1) + color.charAt(2) + color.charAt(2);
    else if (color.length !== 6) throw new Error('Invalid hex color: ' + color);
    const rgb = [];
    for (let i = 0; i <= 2; i++) rgb[i] = Number.parseInt(color.substring(i * 2, 2), 16);

    return { red: rgb[0], green: rgb[1], blue: rgb[2] };
}

export function generateColorFromString(str: string): string {
    let hash = 0;
    str.split('').forEach((char) => {
        const value = char.codePointAt(0) || 0;
        hash = value + ((hash << 5) - hash);
    });
    let colour = '';
    for (let i = 0; i < 3; i++) {
        const value = (hash >> (i * 8)) & 0xff;
        colour += value.toString(16).padStart(2, '0');
    }
    return colour;
}
