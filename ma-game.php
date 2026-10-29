<?php
if (php_sapi_name() !== 'cli') exit("CLI only.\n");

define('C_RED', "\033[31m");
define('C_GREEN', "\033[32m");
define('C_YELLOW', "\033[33m");
define('C_CYAN', "\033[36m");
define('C_RESET', "\033[0m");

function clearScreen() { echo "\033[2J\033[H"; }
function drawCard($card, $hide = false) {
    if ($hide) return "┌───────┐\n│ ????? │\n│ ????? │\n│ ????? │\n└───────┘";
    $rank = $card['rank']; $suit = $card['suit'];
    $color = in_array($suit, ['♥', '♦']) ? C_RED : "\033[37m";
    $r = str_pad($rank, 2, ' ', STR_PAD_LEFT);
    return "┌───────┐\n│ $color$r" . C_RESET . "    │\n│   $color$suit" . C_RESET . "   │\n│    $color$r" . C_RESET . " │\n└───────┘";
}
function calculateHand($hand) {
    $score = 0; $aces = 0;
    foreach ($hand as $card) {
        if ($card['rank'] === 'A') { $aces++; $score += 11; }
        elseif (in_array($card['rank'], ['K', 'Q', 'J'])) $score += 10;
        else $score += (int)$card['rank'];
    }
    while ($score > 21 && $aces > 0) { $score -= 10; $aces--; }
    return $score;
}
function displayGame($playerHand, $dealerHand, $message = "", $revealDealer = false) {
    clearScreen();
    echo C_CYAN . "=== TERMINAL BLACKJACK ===" . C_RESET . "\n\n";
    if ($message) echo C_YELLOW . $message . C_RESET . "\n\n";
    echo "DEALER'S HAND:\n";
    $dealerCards = [];
    foreach ($dealerHand as $index => $card) $dealerCards[] = drawCard($card, ($index === 1 && !$revealDealer));
    for ($i = 0; $i < 5; $i++) { foreach ($dealerCards as $c) echo explode("\n", $c)[$i] . "  "; echo "\n"; }
    echo $revealDealer ? "Score: " . calculateHand($dealerHand) . "\n\n" : "Score: " . calculateHand([$dealerHand[0]]) . " + ?\n\n";
    echo "YOUR HAND:\n";
    $playerCards = array_map(fn($c) => drawCard($c), $playerHand);
    for ($i = 0; $i < 5; $i++) { foreach ($playerCards as $c) echo explode("\n", $c)[$i] . "  "; echo "\n"; }
    echo "Score: " . calculateHand($playerHand) . "\n\n";
}
function playRound() {
    $suits = ['♠', '♥', '♦', '♣']; $ranks = ['2','3','4','5','6','7','8','9','10','J','Q','K','A'];
    $deck = [];
    foreach ($suits as $s) foreach ($ranks as $r) $deck[] = ['suit' => $s, 'rank' => $r];
    shuffle($deck);
    $playerHand = [array_pop($deck), array_pop($deck)];
    $dealerHand = [array_pop($deck), array_pop($deck)];
    displayGame($playerHand, $dealerHand, "Your turn. Hit or Stand?", false);
    while (true) {
        if (calculateHand($playerHand) >= 21) break;
        echo "[H]it or [S]tand: ";
        $input = strtoupper(trim(fgets(STDIN)));
        if ($input === 'H') { $playerHand[] = array_pop($deck); displayGame($playerHand, $dealerHand, "", false); }
        elseif ($input === 'S') break;
    }
    $pScore = calculateHand($playerHand);
    while (calculateHand($dealerHand) < 17) $dealerHand[] = array_pop($deck);
    displayGame($playerHand, $dealerHand, "Revealing Dealer's hand...", true);
    $dScore = calculateHand($dealerHand);
    echo "\n";
    if ($pScore > 21) echo C_RED . "💥 BUST! You lose." . C_RESET . "\n";
    elseif ($dScore > 21) echo C_GREEN . "🎉 DEALER BUSTED! You win!" . C_RESET . "\n";
    elseif ($pScore > $dScore) echo C_GREEN . "🎉 YOU WIN!" . C_RESET . "\n";
    elseif ($pScore < $dScore) echo C_RED . "💥 Dealer wins." . C_RESET . "\n";
    else echo "🤝 PUSH! It's a tie.\n";
}
while (true) {
    playRound();
    echo "\nPlay again? [Y]es / [N]o: ";
    if (strtoupper(trim(fgets(STDIN))) !== 'Y') { clearScreen(); echo "Thanks for playing! 👋\n\n"; break; }
}