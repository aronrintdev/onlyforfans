<?php
namespace App\Interfaces;

interface Sluggable {

    /**
     * Like Eloquent's first(), but specific to where-by-slug, and throws detailed exception
     */
    public static function findBySlug($slug);

    /**
     * `$sluggableFields` is an Array of table field names to use to create the slug
     */
    public function slugify(array $sluggableFields, string $slugField='slug', bool $makeUnique=true) : string;

    /**
     * Returns array of column names to use to create slug
     */
    public function sluggableFields() : array;

    public static function slugifyByTable(
        string $table,
        array $sluggable,
        string $slugField = 'slug',
        bool $makeUnique = true,
        $dbConnection = null
    ) : string;

    /**
     * Model -name- to slug (was toSlug)
     */
    public static function slugifyModel() : string;

}
