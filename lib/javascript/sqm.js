function bortleToLm(bortle) {
    if (bortle == 1) {
        return 6.6;
    } else if (bortle == 2) {
        return 6.5;
    } else if (bortle == 3) {
        return 6.4;
    } else if (bortle == 4) {
        return 6.1;
    } else if (bortle == 5) {
        return 5.4;
    } else if (bortle == 6) {
        return 4.7;
    } else if (bortle == 7) {
        return 4.2;
    } else if (bortle == 8) {
        return 3.8;
    } else if (bortle == 9) {
        return 3.6;
    } else {
        return "";
    }
}

function bortleToSqm(bortle) {
    if (bortle == 1) {
        return 21.85;
    } else if (bortle == 2) {
        return 21.6;
    } else if (bortle == 3) {
        return 21.4;
    } else if (bortle == 4) {
        return 20.85;
    } else if (bortle == 5) {
        return 19.75;
    } else if (bortle == 6) {
        return 18.8;
    } else if (bortle == 7) {
        return 18.25;
    } else if (bortle == 8) {
        return 17.75;
    } else if (bortle == 9) {
        return 17.5;
    } else {
        return "";
    }
}

function lmToSqm(lm) {
    sqm = Math.round(((21.58 - 5 * log10(pow(10, (1.586 - lm / 5.0)) - 1.0))) * 100) / 100; 
    if (sqm > 22.0) {
        sqm = 22.0;
    }
    return sqm;
}

function sqmToBortle(sqm) {
    if (sqm <= 17.5) {
        return 9;
    } else if (sqm <= 18.0) {
        return 8;
    } else if (sqm <= 18.5) {
        return 7;
    } else if (sqm <= 19.1) {
        return 6;
    } else if (sqm <= 20.4) {
        return 5;
    } else if (sqm <= 21.3) {
        return 4;
    } else if (sqm <= 21.5) {
        return 3;
    } else if (sqm <= 21.7) {
        return 2;
    } else {
        return 1;
    }
}

function sqmToLm(sqm) {
    lm = Math.round((7.97 - 5 * log10(1 + pow(10, 4.316 - sqm / 5.0))) * 10) / 10; 
    if (lm < 2.5) {
        lm = 2.5;
    }
    return lm;
}