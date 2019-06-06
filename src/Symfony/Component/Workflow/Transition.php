<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Workflow;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Workflow\Routing\Place;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class Transition
{
    private $name;
    private $froms;
    private $tos;
    private $expressionLanguage;

    /**
     * @param string                  $name
     * @param string|string[]         $froms
     * @param string|string[]         $tos
     * @param ExpressionLanguage|null $expressionLanguage
     */
    public function __construct(string $name, $froms, $tos, ExpressionLanguage $expressionLanguage = null)
    {
        $this->name = $name;

        $this->froms = $this->castToPlace($froms);
        $this->tos = $this->castToPlace($tos);
        $this->expressionLanguage = $expressionLanguage ?: new ExpressionLanguage();
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFroms($subject = null)
    {
        return $this->castPlacesToString($this->filterPlaces($this->froms, $subject));
    }

    public function getTos($subject = null)
    {
        return $this->castPlacesToString($this->filterPlaces($this->tos, $subject));
    }

    /**
     * @param mixed|null $subject
     *
     * @return Place[]
     */
    public function getToPlaces($subject = null): array
    {
        return $this->filterPlaces($this->tos, $subject);
    }

    /**
     * @param mixed|null $subject
     *
     * @return Place[]
     */
    public function getFromPlaces($subject = null): array
    {
        return $this->filterPlaces($this->froms, $subject);
    }

    private function castToPlace($places): array
    {
        $places = (array)$places;
        foreach ($places as $i => $place) {
            if (!$place instanceof Routing\Place) {
                if (is_array($place)) {
                    $places[$i] = new Routing\Place($place[0], $place[1]);
                } else {
                    $places[$i] = new Routing\Place($place);
                }
            }
        }

        return $places;
    }

    /**
     * @param Place[] $places
     * @param mixed|null $subject
     *
     * @return Place[]
     */
    private function filterPlaces($places, $subject = null): array
    {
        return array_filter(
            $places,
            function (Place $place) use ($subject) {
                $condition = $place->getCondition();
                if (null === $condition || null === $subject) {
                    return true;
                }

                return $this->expressionLanguage->evaluate(
                    $place->getCondition(),
                    [
                        'subject' => $subject,
                    ]
                );
            }
        );
    }

    /**
     * @param Place[] $places
     *
     * @return string[]
     */
    private function castPlacesToString($places): array
    {
        return array_map(
            static function (Place $place) {
                return $place->getPlaceName();
            },
            $places
        );
    }
}
