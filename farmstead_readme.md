# AncestorBot Farmstead Minigame
> Discord Chatroom Minigame, in which you go into the Farmstead solo as a randomly selected Hero. You will fight endless battles against Husks and **Lost in Time** heroes, collect trinkets and encounter random events. At your inevitable end, you`ll be rewarded with title and with showcase of your deadly accomplishments.

Table of Contents
-----------------

1. [Disclaimer](#Disclaimer)
2. [Starting the game](#Starting-the-game)
3. [Playing the game](#Playing-the-game)
    * [Example](#example-of-a-started-game)
    * [Table: Available commands while playing](#available-commands-while-playing)
4. [Timeout](#Timeout)
5. [Trinkets](#Trinkets)
6. [Lost in Time](#lost-in-time)
7. [Events](#Events)
8. [Lighting Changes](#Lighting-Changes)
9. [Credits](#Credits)


# Disclaimer
> This is a fan project, not aimed to bring the author any financial returns. All assets used are taken from the [Official Darkest Dungeon Wiki](https://darkestdungeon.gamepedia.com/Darkest_Dungeon_Wiki "Official Darkest Dungeon Wiki"), content of which is available under [CC BY-NC-SA 3.0](https://creativecommons.org/licenses/by-nc-sa/3.0/ "CC BY-NC-SA 3.0"). In no way this Discord Bot Minigame is aimed to replace Darkest Dungeon title by Red Hook Studios or provide a similar experience.

> While most of mechanics, heroes and hero abilities are taken from the Darkest Dungeon game, a lot of values had to be changed to better suit solo "one-vs-one" style of the game. While some familiarity will occur, don't expect everything to work the same way as it works in the actual game. Again, this isn't meant to be a direct copy of the Red Hook game, nor can I ever even aim for it to be that.

> With all that said, I hope you will have fun playing and exploring this fan minigame!
------------
## Starting the game
 1. Add AncestorBot to your Discord server. Instructions on how to do it can be found [here.](README.md#invite-url "here.")
 2. Start the game by typing one of the following commands in chat:
 
| Minigame commands  |
| :------------ |
|  !fight |
|  !f |
|  !df |
|  !dfight |

Option: typing ``!f [CLASS_NAME]``(no brackets, use underscores instead of spaces) will start game with a specified class, instead of randomly selecting one.

>###### Note:
>All of the commands above provide identical function, since they are just aliases for the single *Fight* command.
>Since  **``!f``** is the shortest one, it will be used for the purposes of this documentation. It is also used in in-game tips for the same reason.


## Playing the game

After sending the command, AncestorBot will send a message, which will contain:
1. Your hero\`s description and icon.
2. Description of the first monster that you will fight.
3. Monster statuses such as ``stealth`` or ``riposte`` (Optionally, if they start with one).
4. Monster\`s turn (Optionally, if the monster won the coin flip of who goes first).
5. Monster\'s image.
6. Your available actions in the *footer* , which is the very bottom part of the message.

##### Example of a started game:
![Example of a started game](readme/data/farmstead_start.png "Example of a started game")

Numbers indicate parts of the message from the list above.


### Available commands while playing:

| Command  | Result  | Will end your turn?  |
| :------------: | :------------: | :------------: |
|  **``!f [name of an action in the footer]``**  | Your hero will try to perform the corresponding action  |  Yes |
|   **``!f pass turn``** | Your hero will do nothing this turn and will suffer stress damage for it  | Yes  |
|  **``!f help``**  | A short help, containing all the available *Fight* commands and a link to this very page|  No |
|  **``!help f``**  |  Same as  **``!f help``** | No |
|  **``!f stats``**  |  AncestorBot will try to send you a direct message, containing all the current stats and bonuses of your hero| No |
|  **``!f actions``**  |  AncestorBot will try to send you a direct message, containing descriptions of all actions available to your hero| No |
|  **``!f ff``**  |  Surrender. Your current game will be terminated and you will be presented with an end screen.| Totally |

If a command ends your turn, your hero will perform the corresponding action and then it will be the monster\`s turn again. AncestorBot will send you the results of your\`s and monster\`s actions. 
After that, you are free again to use any of commands listed above.
> ### Important note!
>You don\`t have to type in the full name of an action. Instead, you can provide only a part of the name and the bot will try to find the **first** available to your hero action, which name contains provided sequence of characters.
>Example: your hero\`s available actions are: `aimed shot`, `smokescreen`, `call the shot`, `patch up`, `skeet shot`, `pass turn`. You want your hero to use `call the shot`. Instead of typing the full ``!f call the shot``, you can simply type ``!f ca`` and it will return the same result, since `call the shot` is the first action in the list containing \`\`ca\`\`.
>The same also applies to event actions.

## Timeout
The game will be automatically terminated after 10 minutes of inactivity. 

## Trinkets
After dealing with your first monster, you\`ll find a trinket after killing each subsequent opponent. The bot will show you the image and the description of the new trinket, and your options will be:

|  Command | Result  |
| :------------: | :------------: |
|  **``!f 1``**  |  Your hero will equip trinket in the first trinket slot, possibly replacing already equipped one there |
|  **``!f 2``**  |  Your hero will equip trinket in the second trinket slot, possibly replacing already equipped one there |
| **``!f skip``**  |  Your hero will ignore the trinket and instead will use the available time to heal themselves. The healing amount is random, though the maximum is dependent on the trinket\`s rarity |

Trinkets provide permanent boosts to your hero\`s stats, until they are either replaced by another trinket, or lost during an event.

> ###### Note:
> While deciding whether or not to equip a trinket, you can still use `!f actions` or `!f stats` commands to check your conditions.

## *Lost in Time*
As you proceed further and further, after a certain threshold you will start to encounter *Lost in Time* versions of heroes. Those are always hostile and fights with them proceed as with usual monsters, with the only exception being that *Lost in Time* heroes have deathblow resistance (though much lower than yours), and may not die as soon as their HP reaches 0.

## Events
After dealing with a substantial amount of monsters, you will begin to encounter non-combat events, in which all kinds of things could happen! You may be granted a long-lasting status effect, or lose all your trinkets, or get beaten up, or...
There is a number of pre-written events, though outcomes of some can vary drastically depending on chance or on class of your hero.
Actions in events are different from your usual hero\`s actions, but are selected and performed in the similar fashion by typing **`!f [action name]`**. Available actions are almost always in the footer of the latest game message from bot.

## Lighting Changes
At certain kill intervals, colors of the surroundings will change. This change will affect all monsters' and heroes' stats up until the next interval.   

## Credits
Darkest Dungeon is a game made by Red Hook Studios.

AncestorBot is a Discord bot made by KolFoxy using [DiscordPHP](https://github.com/discord-php/DiscordPHP "DiscordPHP") and other PHP libraries specified in [Composer file](composer.lock "composer.lock").