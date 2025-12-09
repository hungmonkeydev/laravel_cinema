<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    /**
     * Display a listing of movies.
     */
    public function index()
    {
        $movies = Movie::orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $movies,
        ], 200);
    }

    /**
     * Store a newly created movie.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'title_vi' => 'required|string|max:255',
            'description' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'genre' => 'required|string',
            'duration_minutes' => 'required|integer|min:1',
            'release_date' => 'required|date',
            'director' => 'nullable|string',
            'cast' => 'nullable|string',
            'rating' => 'nullable|numeric|between:0,10',
            'poster_url' => 'nullable|url|max:500',
            'trailer_url' => 'nullable|url|max:500',
            'status' => 'required|in:showing,upcoming,ended',
            'language' => 'nullable|string',
            'age_rating' => 'nullable|in:C13,C16,C18,P',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $movie = Movie::create($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $movie,
            'message' => 'Movie created successfully.'
        ], 201);
    }

    /**
     * Display the specified movie.
     */
    public function show($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $movie,
        ], 200);
    }

    /**
     * Update the specified movie.
     */
    public function update(Request $request, $id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'title_vi' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'description_vi' => 'nullable|string',
            'genre' => 'sometimes|required|string',
            'duration_minutes' => 'sometimes|required|integer|min:1',
            'release_date' => 'sometimes|required|date',
            'director' => 'nullable|string',
            'cast' => 'nullable|string',
            'rating' => 'nullable|numeric|between:0,10',
            'poster_url' => 'nullable|url|max:500',
            'trailer_url' => 'nullable|url|max:500',
            'status' => 'sometimes|required|in:showing,upcoming,ended',
            'language' => 'nullable|string',
            'age_rating' => 'nullable|in:C13,C16,C18,P',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $movie->update($validator->validated());

        return response()->json([
            'success' => true,
            'data' => $movie,
            'message' => 'Movie updated successfully.'
        ], 200);
    }

    /**
     * Remove the specified movie.
     */
    public function destroy($id)
    {
        $movie = Movie::find($id);

        if (!$movie) {
            return response()->json([
                'success' => false,
                'message' => 'Movie not found.'
            ], 404);
        }

        $movie->delete();

        return response()->json([
            'success' => true,
            'message' => 'Movie deleted successfully.'
        ], 200);
    }
}
