<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessEmployees;
use App\Models\JobBatch;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;

class UploadController extends Controller
{
    // For Main Page View
    public function index()
    {
        return view('upload');
    }

    // For file upload process Progress
    public function progress()
    {
        return view('progress');
    }

    public function uploadFileAndStoreInDatabase(Request $request)
    {
        try {
            if ($request->has('csvFile')) {
                $fileName = $request->csvFile->getClientOriginalName();
                $fileWithPath = public_path('uploads').'/'.$fileName;

                if (!file_exists($fileWithPath)) {
                    $request->csvFile->move(public_path('uploads'), $fileName);
                }

                $header = null;
                $dataFromcsv = array();
                $records = array_map('str_getcsv', file($fileWithPath));

                // Re-arrangin the data
                foreach ($records as $record) {
                    if (!$header)
                    {
                        $header = $record;
                    }
                    else
                    {
                        $dataFromcsv[] = $record;
                    }
                }

                // 1000 = 300/300/300/100
                // Breaking data, for example 10k to 5k/300
                $dataFromcsv = array_chunk($dataFromcsv, 300);

                // Using Batch
                $batch = Bus::batch([])->dispatch();


                // Looping through each 5000/300 employees
                foreach ($dataFromcsv as $index => $dataCsv)
                {
                    // Looping through each employee data
                    foreach ($dataCsv as $data)
                    {
                        $employeeData[$index][] = array_combine($header, $data);
                    }

                    $batch->add(new ProcessEmployees($employeeData[$index]));
                    // ProcessEmployees::dispatch($employeeData[$index]);
                }


                // We update session id every time we process new batch
                session()->put('lastBatchId', $batch->id);

                // return $batch;
                return redirect('/progress?id='.$batch->id);

            }
        }
        catch (Exception $e) {
            Log::error($e);
            dd($e);
        }
    }


    /**
     * Function gets the progress while obs execute.
     */
    public function progressForScvStoreProcess(Request $request)
    {
        try
        {
            $batchId = $request->id ?? session()->get('lastBatchId');
            // dd($batchId);
            if (JobBatch::where('id', $batchId)->count())
            {
                $response = JobBatch::where('id', $batchId)->first();
                return response()->json($response);
            }
        }
        catch (Exception $e)
        {
            Log::error($e);
            dd($e);
        }
    }
}
