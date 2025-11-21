<?php
require_once '../../config/discord.php';

$discordAuth = new DiscordAuth();
header('Location: ' . $discordAuth->getAuthUrl());
exit();
?>