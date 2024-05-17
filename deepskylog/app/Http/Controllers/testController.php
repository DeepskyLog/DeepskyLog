<?php

namespace App\Http\Controllers;

use App\Http\Requests\testRequest;
use App\Http\Resources\testResource;
use App\Models\test;

class testController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', test::class);

        return testResource::collection(test::all());
    }

    public function store(testRequest $request)
    {
        $this->authorize('create', test::class);

        return new testResource(test::create($request->validated()));
    }

    public function show(test $test)
    {
        $this->authorize('view', $test);

        return new testResource($test);
    }

    public function update(testRequest $request, test $test)
    {
        $this->authorize('update', $test);

        $test->update($request->validated());

        return new testResource($test);
    }

    public function destroy(test $test)
    {
        $this->authorize('delete', $test);

        $test->delete();

        return response()->json();
    }
}
