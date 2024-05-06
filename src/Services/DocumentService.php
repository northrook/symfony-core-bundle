<?php

namespace Northrook\Symfony\Core\Services;

use Northrook\Support\Filter;
use Northrook\Support\Str;
use Northrook\Symfony\Assets\Script;
use Northrook\Symfony\Assets\Stylesheet;
use Northrook\Symfony\Latte\Variables\Document;

final class DocumentService
{

    public const PUBLIC  = 'index, follow';
    public const PRIVATE = 'noindex, nofollow';

    private readonly string $publicDir;
    private readonly string $routeId;
    /**
     * @var array {name: string, value: string|array}
     */
    private array $meta           = [];
    private array $bodyAttributes = [];
    /** @var array {name: string, value: array} */
    private array $script = [];
    /** @var array {name: string, value: array} */
    private array $stylesheet = [];
    /** @var array {name: string, value: array} */
    private array $schema = [];
    public bool   $public = false;

    public function __construct(
        private readonly CurrentRequestService $request,
    ) {}

    public function getParameterObject() : Document {

        if ( $this->public === false ) {
            $this->meta[ 'robots' ][ 'robots' ] = $this::PRIVATE;
        }

        if ( !isset( $this->bodyAttributes[ 'id' ] ) ) {
            $this->bodyAttributes = [ 'id' => $this->getIdFromRoute(), ... $this->bodyAttributes ];
        }

        if ( !isset( $this->meta[ 'title' ] ) ) {
            $this->meta[ 'title' ] = ucwords( trim( str_replace( '/', ' ', $this->request->pathInfo ) ) );
        }

        return new Document(
            $this->meta,
            $this->stylesheet,
            $this->script,
            $this->bodyAttributes,
        );
    }

    public function getMetaTags() : array {
        if ( !isset( $this->meta[ 'robots' ] ) ) {
            $this->meta[ 'robots' ] = $this->public ? $this::PUBLIC : $this::PRIVATE;
        }
        return $this->meta;
    }

    public function getStylesheets() : array {
        return $this->stylesheet;
    }

    public function getScripts() : array {
        return $this->script;
    }

    private function getIdFromRoute() : ?string {
        return $this->routeId ??= Str::key( $this->request->pathInfo, '-' );
    }

    public function body( ...$set ) : self {

        foreach ( $set as $attribute => $value ) {
            $name = strtolower( trim( str_replace( '_', '-', $attribute ), " \n\r\t\v\0-" ) );
            if ( is_bool( $value ) ) {
                $value = $value ? 'true' : 'false';
            }
            elseif ( $name === 'style' && is_array( $value ) ) {
                $style = '';
                foreach ( $value as $key => $val ) {
                    $style .= "$key: $val;";
                }
                $value = $style;
            }
            elseif ( $name === 'class' && is_array( $value ) ) {
                $value = array_flip( array_flip( array_filter( $value ) ) );
            }
            $this->bodyAttributes[ $name ] = $value;
        }

        return $this;
    }

    public function public( bool $set ) : self {

        $this->meta[ 'robots' ] = $set ? 'noindex' : 'index';

        return $this;
    }

    public function title( string $set ) : self {

        $this->meta[ 'title' ] = Filter::string( $set );

        return $this;
    }

    public function description( string $set ) : self {

        $this->meta[ 'description' ] = Filter::string( $set );

        return $this;
    }

    public function author( string $set ) : self {

        $this->meta[ 'author' ] = Filter::string( $set );

        return $this;
    }

    public function keywords( string $set ) : self {

        $this->meta[ 'keywords' ] = Filter::string( $set );

        return $this;
    }

    public function robots( string $set, ?string $name = null ) : self {

        $this->meta[ 'robots' ][ $name ] = $set;

        return $this;
    }

    public function stylesheet( string $path, ?string $id = null ) : self {
        $asset = new Stylesheet( source : $path, name : $id );

        $this->stylesheet[] = [
            'id'   => $asset->name,
            'href' => $asset,
            'rel'  => 'stylesheet',
        ];
        return $this;
    }

    public function script( string $path, bool $defer = true, ?string $id = null ) : self {
        $asset = new Script( source : $path, name : $id );

        $this->script[] = [
            'id'    => $asset->name,
            'src'   => $asset,
            'defer' => $defer,
        ];
        return $this;
    }

}