<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Data;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DataController extends Controller
{
    public function importCsv(Request $request)
{
    $validator = Validator::make($request->all(), [
        'file' => 'required|mimes:csv,txt',
    ]);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 422);
    }

    if ($file = $request->file('file')) {
        $path = $file->getRealPath();
        $data = array_map('str_getcsv', file($path));

        $header = array_shift($data);
        $insertData = [];

        foreach ($data as $row) {
            $rowData = array_combine($header, $row);

            $rowData = array_map(function($value) {
                return $value === '' ? null : $value;
            }, $rowData);

            try {
                $added = !empty($rowData['added']) ? Carbon::createFromFormat('F, d Y H:i:s', $rowData['added']) : null;
                $published = !empty($rowData['published']) ? Carbon::createFromFormat('F, d Y H:i:s', $rowData['published']) : null;

                $insertData[] = [
                    'end_year' => $rowData['end_year'],
                    'citylng' => $rowData['citylng'],
                    'citylat' => $rowData['citylat'],
                    'intensity' => $rowData['intensity'],
                    'sector' => $rowData['sector'],
                    'topic' => $rowData['topic'],
                    'insight' => $rowData['insight'],
                    'swot' => $rowData['swot'],
                    'url' => $rowData['url'],
                    'region' => $rowData['region'],
                    'start_year' => $rowData['start_year'],
                    'impact' => $rowData['impact'],
                    'added' => $added,
                    'published' => $published,
                    'city' => $rowData['city'],
                    'country' => $rowData['country'],
                    'relevance' => $rowData['relevance'],
                    'pestle' => $rowData['pestle'],
                    'source' => $rowData['source'],
                    'title' => $rowData['title'],
                    'likelihood' => $rowData['likelihood'],
                ];
            } catch (\Exception $e) {
                Log::error('Data handling error: ' . $e->getMessage());
                continue;
            }
        }

        try {
            Data::insert($insertData);
        } catch (\Exception $e) {
            Log::error('Database batch insert error: ' . $e->getMessage());
        }

        return response()->json(['success' => 'Data imported successfully.'], 200);
    }

    return response()->json(['error' => 'File not uploaded.'], 400);
}
public function getSector()
{
    $data = DB::table('data')
    ->select('sector')
    ->selectRaw('COUNT(*) AS sector_count')
    ->selectRaw('AVG(intensity) AS avg_intensity')
    ->selectRaw('AVG(likelihood) AS avg_likelihood')
    ->selectRaw('AVG(relevance) AS avg_relevance')
    ->groupBy('sector')
    ->get();

    return response()->json($data);
}
public function getSWOTPestle()
{
        $data = DB::table('data')
            ->select(
                'pestle',
                DB::raw('COUNT(CASE WHEN swot = \'Strength\' THEN 1 END) AS Strength'),
                DB::raw('COUNT(CASE WHEN swot = \'Weakness\' THEN 1 END) AS Weakness'),
                DB::raw('COUNT(CASE WHEN swot = \'Opportunity\' THEN 1 END) AS Opportunity'),
                DB::raw('COUNT(CASE WHEN swot = \'Threat\' THEN 1 END) AS Threat')
            )
            ->whereNotNull('pestle')
            ->whereNotNull('swot')
            ->groupBy('pestle')
            ->orderBy('pestle')
            ->get();

        return response()->json($data);
    }
    public function getCityData()
    {
        $data = DB::select("
            SELECT 
                citylng, 
                citylat, 
                city, 
                AVG(COALESCE(impact, 0)) AS avg_impact, 
                AVG(COALESCE(intensity, 0)) AS avg_intensity 
            FROM 
                data 
            WHERE 
                citylng IS NOT NULL 
                AND citylat IS NOT NULL 
            GROUP BY 
                citylng, citylat, city;
        ");

        return response()->json($data);
    }
    public function getCountry()
    {
        $data = DB::table('data')
            ->select(
                DB::raw('country'),
                DB::raw('AVG(COALESCE(impact, 0)) AS avg_impact'),
                DB::raw('AVG(COALESCE(intensity, 0)) AS avg_intensity')
            )
            ->whereNotNull('country')
            ->groupBy('country')
            ->get();
        return response()->json($data);
    }
    public function getRegion()
    {
        $data = DB::table('data')
            ->select(
                DB::raw('region'),
                DB::raw('AVG(COALESCE(impact, 0)) AS avg_impact'),
                DB::raw('AVG(COALESCE(intensity, 0)) AS avg_intensity')
            )
            ->whereNotNull('region')
            ->groupBy('region')
            ->get();
        return response()->json($data);
    }
    public function getPestleData()
    {
        $results = DB::table(DB::raw('(SELECT
                p.pestle,
                COUNT(DISTINCT p.sector) AS sector_count,
                GROUP_CONCAT(DISTINCT CONCAT(p.sector, ": ", COALESCE(t.topic_count, 0)) ORDER BY p.sector SEPARATOR ", ") AS sectors_with_counts,
                GROUP_CONCAT(DISTINCT CONCAT(p.sector, ": ", COALESCE(t.topic_count, 0)) ORDER BY p.sector SEPARATOR ", ") AS sector_topic_counts,
                SUM(COALESCE(t.topic_count, 0)) AS total_topic_count_with_pestle,
                GROUP_CONCAT(DISTINCT CONCAT(p.sector, ": ", COALESCE(s.source_count, 0)) ORDER BY p.sector SEPARATOR ", ") AS sector_source_counts,
                SUM(COALESCE(s.source_count, 0)) AS total_source_count_with_pestle
            FROM
                (SELECT
                    pestle,
                    sector
                 FROM
                    data
                 GROUP BY
                    pestle, sector
                ) p
            LEFT JOIN
                (SELECT
                    pestle,
                    sector,
                    COUNT(topic) AS topic_count
                 FROM
                    data
                 GROUP BY
                    pestle, sector
                ) t
            ON
                p.pestle = t.pestle AND p.sector = t.sector
            LEFT JOIN
                (SELECT
                    pestle,
                    sector,
                    COUNT(source) AS source_count
                 FROM
                    data
                 GROUP BY
                    pestle, sector
                ) s
            ON
                p.pestle = s.pestle AND p.sector = s.sector
            WHERE
                p.pestle IS NOT NULL
            GROUP BY
                p.pestle
            ORDER BY
                p.pestle
            LIMIT 0, 25) AS query_table'))
            ->get();
        return response()->json($results);
    }
    public function getYearData()
{
    $result = DB::table('data')
        ->select(DB::raw('end_year, pestle, AVG(intensity) as avg_intensity'))
        ->whereNotNull('pestle')
        ->groupBy('end_year', 'pestle')
        ->orderBy('end_year')
        ->get();

    return response()->json($result);
}
}
