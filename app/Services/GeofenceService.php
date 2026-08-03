<?php

namespace App\Services;

use App\Models\AbsensiLokasi;

class GeofenceService
{
    /**
     * Calculate Haversine distance in meters between two lat/lng points.
     */
    public function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusMeters = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusMeters * $c;
    }

    /**
     * Validate geofence submission coordinates against official school location.
     */
    public function validateLocation(?float $latitude, ?float $longitude, ?float $accuracy = null): array
    {
        if ($latitude === null || $longitude === null) {
            return [
                'distance_meters' => null,
                'is_valid' => false,
                'validation_status' => 'location_unavailable',
            ];
        }

        // Validate coordinate bounds
        if ($latitude < -90.0 || $latitude > 90.0 || $longitude < -180.0 || $longitude > 180.0) {
            return [
                'distance_meters' => null,
                'is_valid' => false,
                'validation_status' => 'location_unavailable',
            ];
        }

        // Low accuracy check (> 100 meters accuracy radius)
        if ($accuracy !== null && $accuracy > 100) {
            return [
                'distance_meters' => null,
                'is_valid' => false,
                'validation_status' => 'low_accuracy',
            ];
        }

        $lokasi = AbsensiLokasi::latest()->first();
        $targetLat = $lokasi?->latitude ?? -7.3108238;
        $targetLng = $lokasi?->longitude ?? 112.7292373;
        $maxRadiusKm = $lokasi?->radius ?? 0.2;
        $maxRadiusMeters = $maxRadiusKm * 1000;

        $distanceMeters = $this->calculateDistanceMeters($latitude, $longitude, $targetLat, $targetLng);

        $isWithinRange = $distanceMeters <= $maxRadiusMeters;

        return [
            'distance_meters' => round($distanceMeters, 2),
            'is_valid' => $isWithinRange,
            'validation_status' => $isWithinRange ? 'inside_geofence' : 'outside_geofence',
        ];
    }
}
