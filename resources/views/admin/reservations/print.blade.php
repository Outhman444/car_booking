<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation #{{ $reservation->reservation_number }} - {{ $reservation->user->name ?? 'Guest' }} - {{ $reservation->car->make ?? '' }} {{ $reservation->car->model ?? '' }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .container {
            padding: 30px 40px;
        }
        .header {
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo-section {
            float: left;
            width: 50%;
        }
        .logo {
            height: 45px;
            margin-bottom: 8px;
        }
        .company-name {
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 4px;
            letter-spacing: -0.5px;
        }
        .company-info {
            color: #64748b;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .reservation-meta {
            float: right;
            width: 50%;
            text-align: right;
        }
        .doc-title {
            font-size: 20px;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-style: italic;
        }
        .res-number {
            font-size: 14px;
            color: #3b82f6;
            font-weight: 800;
        }
        .clear {
            clear: both;
        }
        .grid {
            width: 100%;
            margin-bottom: 30px;
        }
        .col {
            float: left;
            padding-right: 20px;
        }
        .col-3 { width: 33.33%; }
        .col-2 { width: 50%; }
        .block-title {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 8px;
            letter-spacing: 1px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
        }
        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            border: 1px solid #f1f5f9;
        }
        .info-row {
            margin-bottom: 6px;
        }
        .info-label {
            color: #64748b;
            font-size: 9px;
            font-weight: 600;
        }
        .info-value {
            font-weight: 800;
            color: #0f172a;
            font-size: 11px;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 25px;
        }
        .table th {
            background: #f8fafc;
            color: #64748b;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: left;
            padding: 12px 15px;
            border-bottom: 2px solid #f1f5f9;
        }
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
            font-weight: 600;
        }
        .text-right { text-align: right; }
        .footer {
            position: fixed;
            bottom: 30px;
            left: 40px;
            right: 40px;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
            color: #94a3b8;
            font-size: 9px;
            text-align: center;
        }
        .summary-box {
            float: right;
            width: 250px;
            background: #f8fafc;
            color: #0f172a;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            margin-top: 10px;
        }
        .summary-row {
            display: block;
            margin-bottom: 8px;
            font-size: 10px;
        }
        .summary-label {
            color: #64748b;
            font-weight: 600;
        }
        .summary-value {
            float: right;
            font-weight: 700;
        }
        .summary-total {
            border-top: 2px solid #cbd5e1;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 15px;
            font-weight: 900;
        }
        .payment-status {
            margin-top: 30px;
            padding: 15px;
            border-radius: 10px;
            background: #f0fdf4;
            border: 1px solid #dcfce7;
        }
        .signature-section {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: left;
            width: 45%;
            height: 100px;
            border: 1px dashed #e2e8f0;
            border-radius: 10px;
            position: relative;
        }
        .signature-label {
            position: absolute;
            bottom: -20px;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                @php
                    $logoPath = public_path('logo/logo.png');
                    if(file_exists($logoPath)) {
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoSrc = 'data:image/png;base64,' . $logoData;
                    } else {
                        $logoSrc = null;
                    }
                @endphp
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" class="logo" alt="Logo">
                @endif
                <div class="company-name">{{ \App\Models\Setting::getValue('site_name', 'Real Rent Car') }}</div>
                <div class="company-info">
                   {{ \App\Models\Setting::getValue('contact_phone', '+1 (555) 000-0000') }} &bull; {{ \App\Models\Setting::getValue('contact_email', 'ops@realrentcar.com') }}
                </div>
            </div>
            <div class="reservation-meta">
                <div class="doc-title">Déclaration de Location</div>
                <div class="res-number">Réf: #{{ $reservation->reservation_number }}</div>
                <div style="margin-top: 8px;">
                    <span class="info-label">Émis le :</span>
                    <span class="info-value">{{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Main Info Grid -->
        <div class="grid">
            <!-- Client Info -->
            <div class="col col-3">
                <div class="block-title">Locataire</div>
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">Nom Complet</div>
                        <div class="info-value">{{ $reservation->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Adresse Email</div>
                        <div class="info-value">{{ $reservation->user->email ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row" style="margin-bottom: 0;">
                        <div class="info-label">Statut du Compte</div>
                        <div class="info-value" style="color: #10b981;">Vérifié Actif</div>
                    </div>
                </div>
            </div>

            <!-- Car Info -->
            <div class="col col-3">
                <div class="block-title">Véhicule Désigné</div>
                <div class="info-card">
                    <div class="info-row">
                        <div class="info-label">Sélection du Modèle</div>
                        <div class="info-value">{{ $reservation->car->year ?? '' }} {{ $reservation->car->make ?? 'N/A' }} {{ $reservation->car->model ?? '' }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label">Plaque d'Immatriculation</div>
                        <div class="info-value" style="color: #3b82f6;">{{ $reservation->car->license_plate ?? 'N/A' }}</div>
                    </div>
                    <div class="info-row" style="margin-bottom: 0;">
                        <div class="info-label">Politique Carburant</div>
                        <div class="info-value">Plein à Plein</div>
                    </div>
                </div>
            </div>

            <!-- Reservation State -->
            <div class="col col-3" style="padding-right: 0;">
                <div class="block-title">Statut de Réservation</div>
                <div class="info-card" style="background: #e0f2fe; border-color: #bae6fd;">
                    @php
                        $statusMap = collect($statusMeta)->keyBy('value');
                        $meta = $statusMap[$reservation->status->value] ?? null;
                        $color = $meta['color'] ?? '#3b82f6';
                    @endphp
                    <div class="info-row" style="margin-bottom: 12px; text-align: center;">
                        <div class="badge" style="background: {{ $color }}; color: #fff; font-size: 11px; padding: 6px 15px;">
                            {{ $meta['label'] ?? ucfirst($reservation->status->value) }}
                        </div>
                    </div>
                    <div class="info-row" style="margin-bottom: 0; text-align: center;">
                        <div class="info-label">Confirmé le</div>
                        <div class="info-value">{{ optional($reservation->created_at)->format('d/m/Y') }}</div>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="block-title">Calendrier de Location</div>
        <table class="table">
            <thead>
                <tr>
                    <th>Étape</th>
                    <th>Date & Heure</th>
                    <th>Lieu Stratégique</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span style="color: #10b981;">&#x25CF;</span> Départ</td>
                    <td>{{ optional($reservation->start_date)->format('l d/m/Y') }} &bull; {{ optional($reservation->pickup_time)->format('H:i') }}</td>
                    <td>{{ $reservation->pickup_location ?? 'Siège de l\'agence' }}</td>
                </tr>
                <tr>
                    <td><span style="color: #ef4444;">&#x25CF;</span> Arrivée</td>
                    <td>{{ optional($reservation->end_date)->format('l d/m/Y') }} &bull; {{ optional($reservation->return_time)->format('H:i') }}</td>
                    <td>{{ $reservation->return_location ?? 'Siège de l\'agence' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary & Totals -->
        <div style="width: 100%;">
            <div style="float: left; width: 50%;">
                <div class="block-title">Vérification du Paiement</div>
                @if($reservation->payments->count() > 0)
                    @foreach($reservation->payments as $p)
                        <div class="info-card" style="margin-bottom: 10px; padding: 10px 15px;">
                            <div class="info-row" style="margin-bottom: 0;">
                                <span class="info-label">Méthode :</span>
                                <span class="info-value">{{ $p->payment_method->label() }}</span>
                                <span class="info-label" style="margin-left: 15px;">Statut :</span>
                                <span class="info-value" style="color: #10b981;">{{ $p->status->label() }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="info-card" style="background: #fff8eb; border-color: #ffedca;">
                        <div class="info-row" style="margin-bottom: 0;">
                            <span class="info-label" style="color: #b45309;">Avis :</span>
                            <span class="info-value" style="color: #b45309;">Aucun paiement numérique détecté. Règlement requis au comptoir via le terminal de l'agence ou en espèces.</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="summary-box" style="margin-top: 20px;">
                <div class="summary-row">
                    <span class="summary-label">Durée de Location</span>
                    <span class="summary-value">{{ $reservation->total_days }} Jour(s)</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Tarif Journalier Moyen</span>
                    <span class="summary-value">{{ $currency }}{{ number_format((float)$reservation->daily_rate, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Sous-total</span>
                    <span class="summary-value">{{ $currency }}{{ number_format((float)$reservation->subtotal, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">TVA & Frais de Service ({{ \App\Models\Setting::getValue('tax_rate', 7) }}%)</span>
                    <span class="summary-value">{{ $currency }}{{ number_format((float)$reservation->tax_amount, 2) }}</span>
                </div>
                @if($reservation->discount_amount > 0)
                <div class="summary-row" style="color: #4ade80;">
                    <span class="summary-label">Promotion / Remise</span>
                    <span class="summary-value">-{{ $currency }}{{ number_format((float)$reservation->discount_amount, 2) }}</span>
                </div>
                @endif
                <div class="summary-total" style="border-top: 1.5px solid #e2e8f0; padding-top: 12px; margin-top: 12px;">
                    <span class="summary-label" style="color: #0f172a; font-size: 13px; font-weight: 900;">Valeur Totale du Contrat</span>
                    <span class="summary-value" style="font-size: 16px;">{{ $currency }}{{ number_format((float)$reservation->total_amount, 2) }}</span>
                </div>

                @php
                    $totalPaid = $reservation->payments()->where('status', \App\Enums\PaymentStatus::COMPLETED)->sum('amount');
                    $balanceDue = max(0, $reservation->total_amount - $totalPaid);
                @endphp

                <div class="summary-row" style="margin-top: 15px; padding: 12px; background-color: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="summary-label" style="color: #10b981; font-weight: 700; font-size: 10px; text-transform: uppercase;">Montant Payé (Acompte)</span>
                        <span class="summary-value" style="color: #10b981; font-weight: 900; font-size: 14px;">{{ $currency }}{{ number_format((float)$totalPaid, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="summary-label" style="color: #ef4444; font-weight: 700; font-size: 10px; text-transform: uppercase;">Solde Restant (À payer à l'agence)</span>
                        <span class="summary-value" style="color: #ef4444; font-weight: 900; font-size: 14px;">{{ $currency }}{{ number_format((float)$balanceDue, 2) }}</span>
                    </div>
                </div>
            </div>
            <div class="clear"></div>
        </div>

        <div class="block-title" style="margin-top: 30px;">Conditions de l'Agence & Documents Requis</div>
        <div class="info-card" style="padding: 15px;">
            <div style="font-size: 10px; white-space: pre-wrap; line-height: 1.6; color: #334155;">{{ \App\Models\Setting::getValue('rental_terms', 'L\'identification physique et un permis de conduire valide sont obligatoires lors du retrait.') }}</div>
            
            @if($reservation->status->value === 'pending' && str_contains($reservation->notes, 'Pay at Agency'))
            <div style="margin-top: 15px; padding-top: 12px; border-top: 1px dashed #cbd5e1; font-weight: 800; color: #b45309; font-size: 10px;">
                EXIGENCE TEMPORELLE : Le paiement en espèces doit être finalisé à l'agence dans les {{ \App\Models\Setting::getValue('cash_reservation_timeout', 24) }} heures suivant la réservation. À défaut, cette réservation sera automatiquement annulée.
            </div>
            @endif
        </div>

        <!-- Signature -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Signature du Client</div>
            </div>
            <div class="signature-box" style="float: right;">
                <div class="signature-label">Signature Autorisée de l'Agence</div>
            </div>
            <div class="clear"></div>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} {{ \App\Models\Setting::getValue('site_name', 'Real Rent Car') }}. Toutes les données sont strictement confidentielles. Généré par le Système de Gestion Executive.
        </div>
    </div>
</body>
</html>
