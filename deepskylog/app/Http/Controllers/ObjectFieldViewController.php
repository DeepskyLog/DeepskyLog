<?php

namespace App\Http\Controllers;

use App\Models\DeepskyObject;
use App\Services\FieldViews\FieldViewBuilder;
use App\Support\SvgRasterizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The view of an object with the naked eye, in the finder and in the
 * telescope (App\Livewire\ObjectFieldViews), and its PDF.
 */
class ObjectFieldViewController extends Controller
{
    /** Query parameters the page and the PDF share. */
    public const OPTIONS = ['set', 'instrument', 'eyepiece', 'lens', 'finder', 'location', 'datetime', 'night'];

    public function show(string $slug)
    {
        $object = $this->object($slug);

        return view('object.field-views', [
            'object'  => $object,
            'backUrl' => route('object.show', ['slug' => $object->slug]),
        ]);
    }

    public function pdf(Request $request, string $slug, FieldViewBuilder $builder)
    {
        $object = $this->object($slug);

        // Square views of 85 mm on A4 landscape, drawn at 144 dpi.
        $sizePx = 480;
        $result = $builder->build($request->user(), $object, $request->only(self::OPTIONS), 'light', $sizePx);

        $panels = [];
        foreach ($result['views'] as $key => $view) {
            if (isset($view['svg'])) {
                $view['image'] = SvgRasterizer::toDataUri($view['svg'], $sizePx, $sizePx)
                    ?? 'data:image/svg+xml;base64,'.base64_encode($view['svg']);
            }
            $panels[$key] = $view;
        }

        $html = view('pdf.field_views', ['result' => $result, 'panels' => $panels])->render();

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $output = $dompdf->output();

        $filename = Str::slug($object->name).'-field-views.pdf';

        return response()->streamDownload(function () use ($output) {
            echo $output;
        }, $filename, ['Content-Type' => 'application/pdf']);
    }

    private function object(string $slug): DeepskyObject
    {
        $object = DeepskyObject::where('slug', $slug)->first()
            ?? DeepskyObject::where('name', $slug)->first();
        abort_unless($object && is_numeric($object->ra) && is_numeric($object->decl), 404);

        return $object;
    }
}
