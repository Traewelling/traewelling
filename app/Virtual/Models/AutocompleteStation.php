<?php

namespace App\Virtual\Models;

use Carbon\Carbon;

/**
 * @OA\Schema (
 *     title="AutocompleteStation",
 *     description="shortened train station model",
 *     @OA\Xml(
 *        name="AutocompleteStation"
 *     )
 * )
 */
class AutocompleteStation
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="Träwelling ID of the station",
     *     example=12345
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="ibnr",
     *     description="IBNR of the station",
     *     example="8000191",
     *     nullable=true
     * )
     *
     * @var int
     */
    private $ibnr;

    /**
     * @OA\Property (
     *     title="rilIdentifier",
     *     description="Identifier specified in 'Richtline 100' of the Deutsche Bahn",
     *     nullable="true",
     *     example="RK"
     * )
     *
     * @var string
     */
    private $rilIdentifier;

    /**
     * @OA\Property(
     *     title="name",
     *     description="name of the station",
     *     example="Karlsruhe Hbf"
     * )
     *
     * @var string
     */
    private $name;

}
