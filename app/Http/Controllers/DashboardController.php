<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\Commande;
use Illuminate\Http\Request;
use MongoDB\Laravel\Auth\User;

class DashboardController extends Controller
{
    public function NbCommandes($id)
    {
        // Obtenir le nombre total de commandes
        $nbCommandesTotal = Commande::where('admin_id', $id)->count();

        // Obtenir les commandes de cette année
        $nbCommandesThisYear = Commande::where('admin_id', $id)
            ->whereYear('created_at', now()->year)
            ->count();

        // Calculer le pourcentage de commandes de cette année par rapport au total
        $percentageThisYear = $nbCommandesTotal > 0 ? ($nbCommandesThisYear / $nbCommandesTotal) * 100 : 0;

        // Obtenir les commandes de l'année précédente
        $nbCommandesLastYear = Commande::where('admin_id', $id)
            ->whereYear('created_at', now()->year - 1)
            ->count();

        // Calculer la différence de commandes par rapport à l'année dernière (positive ou négative)
        $extraCommandes = $nbCommandesThisYear - $nbCommandesLastYear;

        // Si aucune différence de commandes, ne pas montrer l'extra
        $response = [
            'nbCommandesTotal' => $nbCommandesTotal,
            'nbCommandesThisYear' => $nbCommandesThisYear,
            'percentageThisYear' => $percentageThisYear,
        ];

        // Ajouter `extra` et indiquer si c'est un gain ou une perte
        if ($extraCommandes != 0) {
            $response['extra'] = abs($extraCommandes); // Valeur absolue de la différence
            $response['status'] = $extraCommandes > 0 ? 'win' : 'loss'; // Indique gain ou perte
        }

        // Retourner les résultats dans la réponse JSON
        return response()->json($response);
    }

    public function NbClients($id)
{
    // Obtenir le nombre total de clients uniques ayant passé des commandes pour cet admin
    $nbClientsTotal = User::where('role', '2')
        ->where('admin_id', $id)
        ->distinct()
        ->count('id'); // Assurez-vous que 'id' est le champ unique

    // Obtenir les clients uniques de cette année
    $nbClientsThisYear = User::where('role', '2')
        ->where('admin_id', $id)
        ->whereYear('created_at', now()->year)
        ->distinct()
        ->count('id');

    // Calculer le pourcentage de clients de cette année par rapport au total
    $percentageThisYear = $nbClientsTotal > 0 ? ($nbClientsThisYear / $nbClientsTotal) * 100 : 0;

    // Obtenir les clients uniques de l'année précédente
    $nbClientsLastYear = User::where('role', '2')
        ->where('admin_id', $id)
        ->whereYear('created_at', now()->year - 1)
        ->distinct()
        ->count('id');

    // Calculer la différence de clients par rapport à l'année dernière (positive ou négative)
    $extraClients = $nbClientsThisYear - $nbClientsLastYear;

    // Si aucune différence de clients, ne pas montrer l'extra
    $response = [
        'nbClientsTotal' => $nbClientsTotal,
        'nbClientsThisYear' => $nbClientsThisYear,
        'percentageThisYear' => $percentageThisYear,
    ];

    // Ajouter `extra` et indiquer si c'est un gain ou une perte
    if ($extraClients != 0) {
        $response['extra'] = abs($extraClients); // Valeur absolue de la différence
        $response['status'] = $extraClients > 0 ? 'win' : 'loss'; // Indique gain ou perte
    }

    // Retourner les résultats dans la réponse JSON
    return response()->json($response);
}


public function NbFactures($id)
    {
        $nbFacturesTotal = Facture::where('admin_id', $id)->count();

        $nbFacturesThisYear = Facture::where('admin_id', $id)
            ->whereYear('created_at', now()->year)
            ->count();

        $percentageThisYear = $nbFacturesTotal > 0 ? ($nbFacturesThisYear / $nbFacturesTotal) * 100 : 0;

        $nbFacturesLastYear = Facture::where('admin_id', $id)
            ->whereYear('created_at', now()->year - 1)
            ->count();

        $extraFactures = $nbFacturesThisYear - $nbFacturesLastYear;

        $response = [
            'nbFacturesTotal' => $nbFacturesTotal,
            'nbFacturesThisYear' => $nbFacturesThisYear,
            'percentageThisYear' => $percentageThisYear,
        ];

        if ($extraFactures != 0) {
            $response['extra'] = abs($extraFactures);
            $response['status'] = $extraFactures > 0 ? 'win' : 'loss';
        }

        return response()->json($response);
    }

    public function RecentCommandes($id)
    {

        // Get the latest orders
        $commandes = Commande::where('admin_id', $id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json($commandes);
    }

    public function getUsersByCity($id)
    {
        // Retrieve users with role '2' and matching admin_id
        $users = User::where('role', '2')
            ->where('admin_id', $id)
            ->get();

        // Group users by city and count them
        $usersByCity = $users->groupBy('city')->map(function ($group) {
            return $group->count(); // Count users in each city
        });

        // Return the data in the desired format
        return response()->json($usersByCity);
    }
    


}
