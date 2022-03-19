<?php
namespace PHPSTORM_META {
    // Allow PhpStorm IDE to resolve return types when calling book_database( Object_Type::class ) or book_database( `Object_Type` ).
    override(
        \Book_Database\book_database( 0 ),
        map( [
            '' => '@',
            '' => '@Class',
        ] )
    );
}
