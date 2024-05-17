<a
    {{
        $attributes->merge([
            "class" => "inline-flex items-center px-4 py-2 ring-secondary-500 text-white bg-secondary-500 hover:bg-secondary-600 hover:ring-secondary-600
                                                        dark:ring-offset-slate-800 dark:bg-secondary-700 dark:ring-secondary-700
                                                        dark:hover:bg-secondary-600 dark:hover:ring-secondary-600 transition ease-in-out duration-150",
        ])
    }}
>
    {{ $slot }}
</a>
