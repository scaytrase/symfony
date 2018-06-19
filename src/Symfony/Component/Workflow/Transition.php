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

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Grégoire Pineau <lyrixx@lyrixx.info>
 */
class Transition
{
    private $name;
    private $froms;
    private $tos;

    /**
     * @param string          $name
     * @param string|string[] $froms
     * @param string|string[] $tos
     */
    public function __construct(string $name, $froms, $tos)
    {
        $this->name = $name;
        
        $this->froms = $this->castToPlace($froms);
        $this->tos = $this->castToPlace($tos);
    }

    private function castToPlace($places)
    {
        $places = (array) $places;
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

    private function filterPlaces($places, $subject = null)
    {
        $expressionLanguage = new ExpressionLanguage();
        $filteredPlaces = array_filter($places, function ($place) use ($subject, $expressionLanguage) {
            $condition = $place->getCondition();
            if (is_null($condition) || is_null($subject)) {
                return true;
            }
            return $expressionLanguage->evaluate(
                $place->getCondition(),
                array(
                    'subject' => $subject,
                )
            );
        });
        $res = [];
        foreach ($filteredPlaces as $place) {
            $res[] = $place->getPlaceName();
        }
        return $res;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFroms($subject = null)
    {
        return $this->filterPlaces($this->froms, $subject);
    }

    public function getTos($subject = null)
    {
        return $this->filterPlaces($this->tos, $subject);
    }
}
