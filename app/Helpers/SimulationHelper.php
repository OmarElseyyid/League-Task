|<?php

function calculateTeamStrength($teamStrength, $homeAdvantage, $supporterStrength, $goalkeeperFactor)
{
    // Apply adjustments to the team strength based on the provided factors
    $strength = $teamStrength;
    // Apply home advantage
    $strength += $homeAdvantage;
    // Apply supporter strength
    $strength += $supporterStrength;
    // Apply goalkeeper factor
    $strength += $goalkeeperFactor;
    // Return the adjusted team strength
    return $strength;
}


