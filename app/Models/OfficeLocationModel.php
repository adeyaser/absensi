<?php

namespace App\Models;

use CodeIgniter\Model;

class OfficeLocationModel extends Model
{
    protected $table = 'office_locations';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = true;
    protected $allowedFields = ['name', 'address', 'latitude', 'longitude', 'radius', 'is_active'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'latitude' => 'required|decimal',
        'longitude' => 'required|decimal',
        'radius' => 'required|numeric',
    ];

    public function getActive()
    {
        return $this->where('is_active', 1)->findAll();
    }

    /**
     * Check if given coordinates are within any office location
     * 
     * @param float $latitude
     * @param float $longitude
     * @return array|null Office location if within range, null otherwise
     */
    public function checkLocation($latitude, $longitude)
    {
        $locations = $this->getActive();
        
        foreach ($locations as $location) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $location['latitude'],
                $location['longitude']
            );
            
            if ($distance <= $location['radius']) {
                $location['distance'] = $distance;
                return $location;
            }
        }
        
        return null;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * 
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in meters
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // in meters
        
        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);
        
        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;
        
        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($lat1) * cos($lat2) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Get nearest office location
     * 
     * @param float $latitude
     * @param float $longitude
     * @return array|null
     */
    public function getNearestLocation($latitude, $longitude)
    {
        $locations = $this->getActive();
        $nearest = null;
        $minDistance = PHP_FLOAT_MAX;
        
        foreach ($locations as $location) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $location['latitude'],
                $location['longitude']
            );
            
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $location['distance'] = $distance;
                $nearest = $location;
            }
        }
        
        return $nearest;
    }
}
