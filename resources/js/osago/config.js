/**
 * OSAGO Insurance Configuration
 * Contains all constants for insurance calculation and validation
 */

export const CONFIG = {
    // Insurance amounts
    INSURANCE_AMOUNT: 80000000,
    DEFAULT_AMOUNT: 384000,

    // Driver limits
    MAX_DRIVERS: 5,

    // Regional coefficients (КТ - Territory coefficient)
    // According to new regulations: Toshkent shahri va viloyati = 1.2, Boshqalar = 1.0
    REGION_TASHKENT: 1.2,
    REGION_OTHER: 1.0,

    // Vehicle type coefficients (ТБ - Annual base rate)
    // According to new regulations 2026:
    // 1. Yengil avtomobillar: 0.2
    // 2. Yuk avtomobillari: 0.35
    // 3. Avtobuslar va mikroavtobuslar: 0.4
    // 4. Tramvaylar, mototsikllar: 0.075
    VEHICLE_TYPES: {
        1: {
            coef: 0.2,  // Yengil avtomobillar
            labelKey: 'car_type_2'
        },
        6: {
            coef: 0.35,  // Yuk avtomobillari
            labelKey: 'car_type_6'
        },
        9: {
            coef: 0.4,  // Avtobuslar va mikroavtobuslar
            labelKey: 'car_type_9'
        },
        15: {
            coef: 0.075,  // Tramvaylar, mototsikllar
            labelKey: 'car_type_15'
        }
    },

    // Insurance period mapping (coefficient -> months)
    MONTHS_MAP: {
        '1': 12,
        '0.7': 6,
        '0.4': 3
    },

    // Driver limitation coefficients (КБО - Driver restriction coefficient)
    // According to new regulations: Unlimited = 2, Limited = 1
    DRIVER_COEF: {
        UNLIMITED: 2,  // Changed from 3 to 2
        LIMITED: 1
    },

    // КБМ - Accident history coefficient (for limited drivers)
    // O'tgan 12 oy davomida sug'urta hodisalari
    ACCIDENT_COEF: {
        NO_ACCIDENTS: 1.0,      // Birinchi marta yoki hodisa yo'q
        ONE_ACCIDENT: 1.3,      // 1 hodisa
        TWO_ACCIDENTS: 2.0,     // 2 hodisa
        THREE_OR_MORE: 3.0      // 3 va undan ko'p
    },

    // КВ - Driver experience coefficient (Haydovchi staji)
    // Hamma uchun 1.0 (2 yilgacha, 2-5 yil, 5 yildan ortiq)
    EXPERIENCE_COEF: 1.0,

    // КС - Seasonal usage coefficient (Mavsumiy foydalanish)
    SEASONAL_COEF: {
        '0.7': 0.7,  // 6 oy
        '1': 1.0     // 12 oy
    },

    // КП - Foreign registration coefficient (Chet davlatda ro'yxatdan o'tkazilgan)
    FOREIGN_COEF: {
        '15_DAYS': 0.2,   // 15 kungacha
        '2_MONTHS': 0.4,  // 2 oy
        '12_MONTHS': 1.0  // 12 oy
    },

    // КН - Violation coefficient (Qoida buzishlar)
    // Hamma uchun 1.0
    VIOLATION_COEF: 1.0,

    // КВЗ - Driver age coefficient (Haydovchi yoshi)
    // Hamma uchun 1.0 (22 yoshgacha va 22 yoshdan katta)
    AGE_COEF: 1.0,

    // Tashkent region prefixes for government numbers
    TASHKENT_PREFIXES: ['01', '10']
};

/**
 * Get vehicle type label from translations
 * @param {number} typeId - Vehicle type ID (1, 6, 9, 15)
 * @returns {string} Translated label or fallback
 */
export function getVehicleTypeLabel(typeId) {
    const vehicleType = CONFIG.VEHICLE_TYPES[typeId];
    if (!vehicleType) {
        return 'Unknown Type';
    }

    const labelKey = vehicleType.labelKey;
    return window.TRANSLATIONS?.[labelKey] || `Type ${typeId}`;
}

/**
 * Get vehicle type info with translated label
 * @param {number} typeId - Vehicle type ID
 * @returns {object} {coef, label, labelKey}
 */
export function getVehicleType(typeId) {
    const vehicleType = CONFIG.VEHICLE_TYPES[typeId];
    if (!vehicleType) {
        return null;
    }

    return {
        coef: vehicleType.coef,
        label: getVehicleTypeLabel(typeId),
        labelKey: vehicleType.labelKey
    };
}

export default CONFIG;
