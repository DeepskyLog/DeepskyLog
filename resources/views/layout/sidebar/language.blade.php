<li>
    <form role="form" action="/lang" method="POST">
        {{  csrf_field() }}

        @php
        $languages = '';
        foreach(Config::get('laravel-gettext.supported-locales') as $locale) {
        $localeText = ucwords(Locale::getDisplayLanguage($locale, $locale));
        $languages .= '<option value="' . $locale . '"';
        if ($locale==LaravelGettext::getLocale()) {
            $languages .= ' selected="selected"';
        }
        $languages .= '>' . $localeText .'</option>';
        }
        @endphp
        <div x-data=''>
            <div x-data x-init="() => {
                var choices = new Choices($refs.language, {
                    itemSelectText: '',
                });
                choices.passedElement.element.addEventListener(
                  'change',
                  function(event) {
                        values = event.detail.value;
                  },
                  false,
                );
                }">
                <select onchange="this.form.submit()" class="form-control-sm" id="language" name="language"
                    x-ref="language">
                    {!! htmlspecialchars_decode($languages) !!}
                </select>
            </div>
        </div>
    </form>
</li>
<br />
