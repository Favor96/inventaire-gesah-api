<!DOCTYPE html>
<html>
<head>
    <title>Reçu de paiement</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 5px; text-align: left; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reçu de paiement</h2>
    </div>

    <div class="section">
        <strong>Vente ID :</strong> {{ $vente->id }} <br>
        <strong>Client :</strong> {{ $vente->client->nom ?? '' }} <br>
        <strong>Date de vente :</strong> {{ $vente->date_vente->format('d/m/Y H:i') }} <br>
        <strong>Montant total :</strong> {{ number_format($vente->total, 2) }} <br>
    </div>

    <div class="section">
        <strong>Paiement :</strong> <br>
        <strong>Date :</strong> {{ $paiement->date_paiement->format('d/m/Y H:i') }} <br>
        <strong>Montant :</strong> {{ number_format($paiement->montant, 2) }} <br>
        <strong>Mode :</strong> {{ $paiement->mode_paiement }} <br>
        <strong>Référence :</strong> {{ $paiement->reference ?? '-' }} <br>
    </div>

    <div class="section">
        <strong>Lignes de vente :</strong>
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th>Quantité</th>
                    <th>Prix unitaire</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vente->lignesVente as $ligne)
                <tr>
                    <td>{{ $ligne->produit->nom ?? '' }}</td>
                    <td>{{ $ligne->quantite }}</td>
                    <td>{{ number_format($ligne->prix_unitaire,2) }}</td>
                    <td>{{ number_format($ligne->quantite * $ligne->prix_unitaire,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
