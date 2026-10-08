<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'خانەی :attribute دەبێت پەسەند بکرێت.',
    'accepted_if' => 'خانەی :attribute دەبێت پەسەند بکرێت کاتێک :other بریتییە لە :value.',
    'active_url' => 'خانەی :attribute دەبێت بەستەرێکی دروست بێت.',
    'after' => 'خانەی :attribute دەبێت بەروارێک بێت دوای :date.',
    'after_or_equal' => 'خانەی :attribute دەبێت بەروارێک بێت دوای :date یان یەکسان بێت پێی.',
    'alpha' => 'خانەی :attribute تەنها دەبێت پیت لەخۆ بگرێت.',
    'alpha_dash' => 'خانەی :attribute تەنها دەبێت پیت، ژمارە و هێڵ لەخۆ بگرێت.',
    'alpha_num' => 'خانەی :attribute تەنها دەبێت پیت و ژمارە لەخۆ بگرێت.',
    'array' => 'خانەی :attribute دەبێت لیستێک بێت.',
    'ascii' => 'خانەی :attribute تەنها دەبێت پیت و هێمای ئینگلیزی لەخۆ بگرێت.',
    'before' => 'خانەی :attribute دەبێت بەروارێک بێت پێش :date.',
    'before_or_equal' => 'خانەی :attribute دەبێت بەروارێک بێت پێش :date یان یەکسان بێت پێی.',
    'between' => [
        'array' => 'خانەی :attribute دەبێت لە نێوان :min و :max دانە بێت.',
        'file' => 'قەبارەی پەڕگەی :attribute دەبێت لە نێوان :min و :max کیلۆبایت بێت.',
        'numeric' => 'خانەی :attribute دەبێت لە نێوان :min و :max بێت.',
        'string' => 'خانەی :attribute دەبێت لە نێوان :min و :max پیت بێت.',
    ],
    'boolean' => 'خانەی :attribute دەبێت ڕاست یان هەڵە بێت.',
    'can' => 'خانەی :attribute نرخێکی ڕێگەپێنەدراوی تێدایە.',
    'confirmed' => 'دووپاتکردنەوەی خانەی :attribute یەک ناگرێتەوە.',
    'contains' => 'خانەی :attribute نرخێکی پێویستی تێدا نییە.',
    'current_password' => 'وشەی نهێنی هەڵەیە.',
    'date' => 'خانەی :attribute دەبێت بەروارێکی دروست بێت.',
    'date_equals' => 'خانەی :attribute دەبێت بەروارێک بێت یەکسان بە :date.',
    'date_format' => 'خانەی :attribute دەبێت لەگەڵ شێوازی :format یەک بگرێتەوە.',
    'decimal' => 'خانەی :attribute دەبێت :decimal ژمارەی دوای فاریزەی هەبێت.',
    'declined' => 'خانەی :attribute دەبێت ڕەت بکرێتەوە.',
    'declined_if' => 'خانەی :attribute دەبێت ڕەت بکرێتەوە کاتێک :other بریتییە لە :value.',
    'different' => 'خانەی :attribute و :other دەبێت جیاواز بن.',
    'digits' => 'خانەی :attribute دەبێت :digits ژمارە بێت.',
    'digits_between' => 'خانەی :attribute دەبێت لە نێوان :min و :max ژمارە بێت.',
    'dimensions' => 'ڕەهەندەکانی وێنەی :attribute دروست نین.',
    'distinct' => 'خانەی :attribute نرخێکی دووبارەی تێدایە.',
    'doesnt_end_with' => 'خانەی :attribute نابێت بە یەکێک لەمانە کۆتایی بێت: :values.',
    'doesnt_start_with' => 'خانەی :attribute نابێت بە یەکێک لەمانە دەست پێ بکات: :values.',
    'email' => ':attribute دەبێت ئیمەیڵێکی دروست بێت.',
    'ends_with' => 'خانەی :attribute دەبێت بە یەکێک لەمانە کۆتایی بێت: :values.',
    'enum' => ':attribute ی هەڵبژێردراو دروست نییە.',
    'exists' => ':attribute ی هەڵبژێردراو دروست نییە.',
    'extensions' => 'خانەی :attribute دەبێت یەکێک لەم پاشگرانەی هەبێت: :values.',
    'file' => 'خانەی :attribute دەبێت پەڕگە بێت.',
    'filled' => 'خانەی :attribute دەبێت نرخی هەبێت.',
    'gt' => [
        'array' => 'خانەی :attribute دەبێت زیاتر لە :value دانەی هەبێت.',
        'file' => 'قەبارەی پەڕگەی :attribute دەبێت زیاتر بێت لە :value کیلۆبایت.',
        'numeric' => 'خانەی :attribute دەبێت زیاتر بێت لە :value.',
        'string' => 'خانەی :attribute دەبێت زیاتر بێت لە :value پیت.',
    ],
    'gte' => [
        'array' => 'خانەی :attribute دەبێت :value دانە یان زیاتری هەبێت.',
        'file' => 'قەبارەی پەڕگەی :attribute دەبێت :value کیلۆبایت یان زیاتر بێت.',
        'numeric' => 'خانەی :attribute دەبێت زیاتر یان یەکسان بێت بە :value.',
        'string' => 'خانەی :attribute دەبێت :value پیت یان زیاتر بێت.',
    ],
    'hex_color' => 'خانەی :attribute دەبێت ڕەنگێکی hex ی دروست بێت.',
    'image' => 'خانەی :attribute دەبێت وێنە بێت.',
    'in' => ':attribute ی هەڵبژێردراو دروست نییە.',
    'in_array' => 'خانەی :attribute دەبێت لە :other دا هەبێت.',
    'integer' => 'خانەی :attribute دەبێت ژمارەیەکی تەواو بێت.',
    'ip' => 'خانەی :attribute دەبێت ناونیشانی IP ی دروست بێت.',
    'ipv4' => 'خانەی :attribute دەبێت ناونیشانی IPv4 ی دروست بێت.',
    'ipv6' => 'خانەی :attribute دەبێت ناونیشانی IPv6 ی دروست بێت.',
    'json' => 'خانەی :attribute دەبێت دەقێکی JSON ی دروست بێت.',
    'list' => 'خانەی :attribute دەبێت لیست بێت.',
    'lowercase' => 'خانەی :attribute دەبێت بە پیتی بچووک بێت.',
    'lt' => [
        'array' => 'خانەی :attribute دەبێت کەمتر لە :value دانەی هەبێت.',
        'file' => 'قەبارەی پەڕگەی :attribute دەبێت کەمتر بێت لە :value کیلۆبایت.',
        'numeric' => 'خانەی :attribute دەبێت کەمتر بێت لە :value.',
        'string' => 'خانەی :attribute دەبێت کەمتر بێت لە :value پیت.',
    ],
    'lte' => [
        'array' => 'خانەی :attribute نابێت زیاتر لە :value دانەی هەبێت.',
        'file' => 'قەبارەی پەڕگەی :attribute دەبێت :value کیلۆبایت یان کەمتر بێت.',
        'numeric' => 'خانەی :attribute دەبێت کەمتر یان یەکسان بێت بە :value.',
        'string' => 'خانەی :attribute دەبێت :value پیت یان کەمتر بێت.',
    ],
    'mac_address' => 'خانەی :attribute دەبێت ناونیشانی MAC ی دروست بێت.',
    'max' => [
        'array' => 'خانەی :attribute نابێت زیاتر لە :max دانەی هەبێت.',
        'file' => 'قەبارەی پەڕگەی :attribute نابێت لە :max کیلۆبایت زیاتر بێت.',
        'numeric' => 'خانەی :attribute نابێت لە :max زیاتر بێت.',
        'string' => 'خانەی :attribute نابێت لە :max پیت زیاتر بێت.',
    ],
    'max_digits' => 'خانەی :attribute نابێت لە :max ژمارە زیاتر بێت.',
    'mimes' => 'خانەی :attribute دەبێت پەڕگەیەک بێت لە جۆری: :values.',
    'mimetypes' => 'خانەی :attribute دەبێت پەڕگەیەک بێت لە جۆری: :values.',
    'min' => [
        'array' => 'خانەی :attribute دەبێت لانیکەم :min دانەی هەبێت.',
        'file' => 'قەبارەی پەڕگەی :attribute دەبێت لانیکەم :min کیلۆبایت بێت.',
        'numeric' => 'خانەی :attribute دەبێت لانیکەم :min بێت.',
        'string' => 'خانەی :attribute دەبێت لانیکەم :min پیت بێت.',
    ],
    'min_digits' => 'خانەی :attribute دەبێت لانیکەم :min ژمارەی هەبێت.',
    'missing' => 'خانەی :attribute نابێت هەبێت.',
    'missing_if' => 'خانەی :attribute نابێت هەبێت کاتێک :other بریتییە لە :value.',
    'missing_unless' => 'خانەی :attribute نابێت هەبێت مەگەر :other بریتی بێت لە :value.',
    'missing_with' => 'خانەی :attribute نابێت هەبێت کاتێک :values هەیە.',
    'missing_with_all' => 'خانەی :attribute نابێت هەبێت کاتێک :values هەن.',
    'multiple_of' => 'خانەی :attribute دەبێت چەندجارەیەکی :value بێت.',
    'not_in' => ':attribute ی هەڵبژێردراو دروست نییە.',
    'not_regex' => 'شێوازی خانەی :attribute دروست نییە.',
    'numeric' => 'خانەی :attribute دەبێت ژمارە بێت.',
    'password' => [
        'letters' => 'خانەی :attribute دەبێت لانیکەم یەک پیتی تێدا بێت.',
        'mixed' => 'خانەی :attribute دەبێت لانیکەم یەک پیتی گەورە و یەک پیتی بچووکی تێدا بێت.',
        'numbers' => 'خانەی :attribute دەبێت لانیکەم یەک ژمارەی تێدا بێت.',
        'symbols' => 'خانەی :attribute دەبێت لانیکەم یەک هێمای تێدا بێت.',
        'uncompromised' => ':attribute لە دزەکردنی زانیاریدا دەرکەوتووە، تکایە :attribute ێکی تر هەڵبژێرە.',
    ],
    'present' => 'خانەی :attribute دەبێت هەبێت.',
    'present_if' => 'خانەی :attribute دەبێت هەبێت کاتێک :other بریتییە لە :value.',
    'present_unless' => 'خانەی :attribute دەبێت هەبێت مەگەر :other بریتی بێت لە :value.',
    'present_with' => 'خانەی :attribute دەبێت هەبێت کاتێک :values هەیە.',
    'present_with_all' => 'خانەی :attribute دەبێت هەبێت کاتێک :values هەن.',
    'prohibited' => 'خانەی :attribute ڕێگەپێنەدراوە.',
    'prohibited_if' => 'خانەی :attribute ڕێگەپێنەدراوە کاتێک :other بریتییە لە :value.',
    'prohibited_if_accepted' => 'خانەی :attribute ڕێگەپێنەدراوە کاتێک :other پەسەند کراوە.',
    'prohibited_if_declined' => 'خانەی :attribute ڕێگەپێنەدراوە کاتێک :other ڕەت کراوەتەوە.',
    'prohibited_unless' => 'خانەی :attribute ڕێگەپێنەدراوە مەگەر :other لەناو :values دا بێت.',
    'prohibits' => 'خانەی :attribute ڕێگە نادات :other هەبێت.',
    'regex' => 'شێوازی خانەی :attribute دروست نییە.',
    'required' => 'خانەی :attribute پێویستە.',
    'required_array_keys' => 'خانەی :attribute دەبێت ئەمانەی تێدا بێت: :values.',
    'required_if' => 'خانەی :attribute پێویستە کاتێک :other بریتییە لە :value.',
    'required_if_accepted' => 'خانەی :attribute پێویستە کاتێک :other پەسەند کراوە.',
    'required_if_declined' => 'خانەی :attribute پێویستە کاتێک :other ڕەت کراوەتەوە.',
    'required_unless' => 'خانەی :attribute پێویستە مەگەر :other لەناو :values دا بێت.',
    'required_with' => 'خانەی :attribute پێویستە کاتێک :values هەیە.',
    'required_with_all' => 'خانەی :attribute پێویستە کاتێک :values هەن.',
    'required_without' => 'خانەی :attribute پێویستە کاتێک :values نییە.',
    'required_without_all' => 'خانەی :attribute پێویستە کاتێک هیچ کام لە :values نییە.',
    'same' => 'خانەی :attribute دەبێت لەگەڵ :other یەک بگرێتەوە.',
    'size' => [
        'array' => 'خانەی :attribute دەبێت :size دانەی تێدا بێت.',
        'file' => 'قەبارەی پەڕگەی :attribute دەبێت :size کیلۆبایت بێت.',
        'numeric' => 'خانەی :attribute دەبێت :size بێت.',
        'string' => 'خانەی :attribute دەبێت :size پیت بێت.',
    ],
    'starts_with' => 'خانەی :attribute دەبێت بە یەکێک لەمانە دەست پێ بکات: :values.',
    'string' => 'خانەی :attribute دەبێت دەق بێت.',
    'timezone' => 'خانەی :attribute دەبێت ناوچەیەکی کاتی دروست بێت.',
    'triple_name' => 'خانەی :attribute دەبێت سێ وشە بێت.',
    'unique' => 'نرخی خانەی :attribute پێشتر بەکار هاتووە',
    'uploaded' => 'بارکردنی :attribute سەرکەوتوو نەبوو.',
    'uppercase' => 'خانەی :attribute دەبێت بە پیتی گەورە بێت.',
    'url' => 'خانەی :attribute دەبێت بەستەرێکی دروست بێت.',
    'ulid' => 'خانەی :attribute دەبێت ULID ی دروست بێت.',
    'uuid' => 'خانەی :attribute دەبێت UUID ی دروست بێت.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'current_count' => 'ژمارەی ئێستا',
        'total_count' => 'کۆی ژمارەی تەسبیحەکان',
        'amount' => 'بڕ',
        'currency' => 'دراو',
        'country' => 'وڵات',
        'name' => 'ناو',
        'email' => 'ئیمەیڵ',
        'phone' => 'ژمارەی مۆبایل',
        'password' => 'وشەی نهێنی',
        'location' => 'شوێنی جوگرافی',
        'latitude' => 'هێڵی پانی',
        'longitude' => 'هێڵی درێژی',
        'avatar' => 'وێنە',
        'code' => 'کۆدی پشتڕاستکردنەوە',
    ],

];
