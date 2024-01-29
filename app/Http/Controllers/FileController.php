<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\UpdateFileRequest;
use App\Models\Task;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Task $task)
    {
        foreach ($request->file('files') as $uploadedFile) {
            logger('Uploading file: ' . $uploadedFile->getClientOriginalName()); 
    
            $fileName = auth()->id() . '-projects-' . time() . '-' . $uploadedFile->getClientOriginalName();
            $filePath = $uploadedFile->storeAs('projectfiles', $fileName, 'public');
    
            File::create([
                'model_id' => $task->id, 
                'model_type' => Task::class,
                'filename' => $uploadedFile->getClientOriginalName(),
                'file_path' => $filePath,
                'name' => $uploadedFile->getClientOriginalName(),
                'type' => $uploadedFile->getClientMimeType(),
                'size' => $uploadedFile->getSize(),
                'user_id' => auth()->id(),
                'department_id' => auth()->user()->department_id,
            ]);
        }
    
        return back()->with('success', 'Files uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateFileRequest $request, File $file)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }
}
