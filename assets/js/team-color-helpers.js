const DEFAULT_TEAM_COLOR = '#2563eb';

// Darker floor so mid-grays like #7d7d7d do not cliff-drop vs #b8b8b8.
const TEXT_BLACK_WEIGHT_MIN = 0.36;
const TEXT_BLACK_WEIGHT_MAX = 0.58;

const BORDER_BLACK_WEIGHT_MIN = 0.22;
const BORDER_BLACK_WEIGHT_MAX = 0.4;

// Wide smooth ramp: gentle at #7d7d7d (~0.22 lum), strong by off-whites.
const CURVE_START = 0.18;
const CURVE_END = 0.62;

function isValidTeamColor(color) {
    return /^#[0-9a-f]{6}$/i.test(color || '');
}

function parseTeamHexColor(hex) {
    const match = /^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i.exec(hex || '');

    if (!match) {
        return null;
    }

    return [
        parseInt(match[1], 16),
        parseInt(match[2], 16),
        parseInt(match[3], 16),
    ];
}

function getTeamColorRelativeLuminance(hex) {
    const rgb = parseTeamHexColor(hex);

    if (!rgb) {
        return 0;
    }

    const channels = rgb.map((channel) => {
        const normalized = channel / 255;
        return normalized <= 0.03928
            ? normalized / 12.92
            : Math.pow((normalized + 0.055) / 1.055, 2.4);
    });

    return 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
}

function smoothstep(edge0, edge1, value) {
    const t = Math.min(Math.max((value - edge0) / (edge1 - edge0), 0), 1);

    return t * t * (3 - 2 * t);
}

function lerp(start, end, amount) {
    return start + (end - start) * amount;
}

function mixHexColor(hex, targetHex, targetWeight) {
    const source = parseTeamHexColor(hex);
    const target = parseTeamHexColor(targetHex);

    if (!source || !target) {
        return null;
    }

    const weight = Math.min(Math.max(targetWeight, 0), 1);
    const channels = source.map((channel, index) => Math.round(
        channel * (1 - weight) + target[index] * weight
    ));

    return `#${channels
        .map(channel => channel.toString(16).padStart(2, '0'))
        .join('')}`;
}

function getLightnessBlend(luminance) {
    return smoothstep(CURVE_START, CURVE_END, luminance);
}

function getTeamTextColor(hex) {
    const blend = getLightnessBlend(getTeamColorRelativeLuminance(hex));
    const weight = lerp(TEXT_BLACK_WEIGHT_MIN, TEXT_BLACK_WEIGHT_MAX, blend);

    return mixHexColor(hex, '#000000', weight);
}

function getTeamBorderColor(hex) {
    const blend = getLightnessBlend(getTeamColorRelativeLuminance(hex));
    const weight = lerp(BORDER_BLACK_WEIGHT_MIN, BORDER_BLACK_WEIGHT_MAX, blend);

    return mixHexColor(hex, '#000000', weight);
}

function normalizeTeamColor(color) {
    return isValidTeamColor(color) ? color : DEFAULT_TEAM_COLOR;
}

function getTeamBadgeStyle(color) {
    const teamColor = normalizeTeamColor(color);
    const textColor = getTeamTextColor(teamColor);
    const borderColor = getTeamBorderColor(teamColor);
    const styleParts = [`--team-color: ${teamColor}`];

    if (textColor) {
        styleParts.push(`--team-text-color: ${textColor}`);
    }

    if (borderColor) {
        styleParts.push(`--team-border-color: ${borderColor}`);
    }

    return styleParts.join('; ');
}
