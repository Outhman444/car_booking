/**
 * Backend to French Translation Mappings
 * Strictly hardcoded for French-only support
 */

export const reservationStatus: Record<string, string> = {
  pending: 'En attente',
  confirmed: 'Confirmé',
  active: 'Actif',
  completed: 'Terminé',
  cancelled: 'Annulé',
  no_show: 'Non présenté'
};

export const paymentStatus: Record<string, string> = {
  paid: 'Payé',
  unpaid: 'Non payé',
  pay_at_pickup: 'Paiement à l\'agence',
  pending: 'En attente',
  completed: 'Complété',
  failed: 'Échoué',
  cancelled: 'Annulé',
  refunded: 'Remboursé',
  partially_refunded: 'Partiellement remboursé'
};

export const fuelType: Record<string, string> = {
  diesel: 'Diesel',
  petrol: 'Essence',
  gasoline: 'Essence',
  hybrid: 'Hybride',
  plugin_hybrid: 'Hybride Rechargeable',
  'plug-in hybrid': 'Hybride Rechargeable',
  electric: 'Électrique',
  lpg: 'GPL',
  cng: 'GNV',
  hydrogen: 'Hydrogène'
};

export const transmission: Record<string, string> = {
  manual: 'Manuelle',
  automatic: 'Automatique',
  auto: 'Automatique'
};

export const carStatus: Record<string, string> = {
  available: 'Disponible',
  pending: 'En attente',
  reserved: 'Réservé',
  rented: 'Loué',
  maintenance: 'Maintenance',
  out_of_service: 'Hors Service'
};

export const carColor: Record<string, string> = {
  white: 'Blanc',
  black: 'Noir',
  silver: 'Argent',
  gray: 'Gris',
  blue: 'Bleu',
  red: 'Rouge',
  green: 'Vert',
  yellow: 'Jaune',
  brown: 'Marron',
  orange: 'Orange'
};

export const ticketStatus: Record<string, string> = {
  new: 'Nouveau',
  in_progress: 'En Cours',
  closed: 'Fermé'
};

export const paymentMethod: Record<string, string> = {
  paypal: 'PayPal',
  stripe: 'Carte Bancaire',
  cash: 'Espèces (à l\'agence)'
};

/**
 * Helper to translate a key safely
 */
export function trans(category: 'reservation' | 'payment' | 'fuel' | 'transmission' | 'carStatus' | 'color' | 'ticket' | 'paymentMethod', key: string): string {
  if (!key) return '—';
  
  const formattedKey = key.toLowerCase();
  
  const mappings: Record<string, Record<string, string>> = {
    reservation: reservationStatus,
    payment: paymentStatus,
    fuel: fuelType,
    transmission: transmission,
    carStatus: carStatus,
    color: carColor,
    ticket: ticketStatus,
    paymentMethod: paymentMethod
  };

  return mappings[category]?.[formattedKey] || key;
}
