<?php

namespace Northrook\Symfony\Core\Form;

use Countable;

final class Errors implements Countable
{

    /**
     * @var Error[] <fieldName, Error>
     */
    private array $errors = [];

    public function set( Error ...$errors ) : void {
        foreach ( $errors as $error ) {
            $this->errors[ $error->fieldName ] = $error;
        }
    }

    public function add( Error $error ) : void {
        $this->errors[ $error->fieldName ] = $error;
    }

    public function get( string $fieldName ) : ?Error {
        return $this->errors[ $fieldName ] ?? null;
    }

    public function count() : int {
        return count( $this->errors );
    }
}