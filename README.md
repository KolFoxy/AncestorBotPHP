AncestorBotPHP
====
> Discord bot that mimics Ancestor's (from the game Darkest Dungeon) behavior and humour. Tests resolve.

Table of Contents
-----------------

1. [Public Bot Usage](#Public-Bot-Usage) 
    * [Invite URL](#Invite-URL)
    * [Top.gg Page](#Topgg-Page)
    * [Commands](#Commands)
    * [Chat Reactions](#Chat-Reactions)
    * [Command Examples](#Command-Examples)    
    * [Chat Reactions Examples](#Chat-Reactions-Examples)
1. [Credits](#Credits)


## Public Bot Usage
To use AncestorBotPHP, simply invite him to your Discord server via invite URL (provided you are the server administrator or have permission to do so).

### Invite URL
https://discordapp.com/oauth2/authorize?client_id=406438624473907200&scope=bot&permissions=379904

### Top.gg Page
https://top.gg/bot/406438624473907200

-----

### Commands

Command prefix: **`!`** (exclamation mark)

Commands are called by sending a text message containing **`![command name] [arguments (if any)]`** in a chat accesable by the AncestorBot.

|Name   |Description   |Aliases   | Syntax   |
| :------------ | :------------ | :------------ | :------------ |
| help  | Lists all commands if used without arguments. Displays description of a command if used with an apropriate argument.   |   | ** `help`**, **`help [command]`**|
| fight  | Launches the Farmstead minigame. [More information and instructions.](farmstead_readme.md "More information and instructions.")  | f, df, dfight  |**`f`**, **` f [name of an action]`**, **`f pass turn`**, **`f help`**, **`f stats`**, **`f actions`**, **`f ff`**, **`f [class name]`** |
| gold  | Sends picture of a random reward.  |   | **`gold`**  |
| spin | Creates and sends a GIF of a user avatar or of a provided picture spinning inside of Tide™. If no arguments are provided, sender's own avatar is spinned.|   | **`spin`**, **`spin [user mention]`**, **`spin [picture url]`**|
| zalgo  |  Turns given text into zalgo text. Looks something ̝̺̋l̃̊̕i͈͌͡k̛͉̕e̟̩ͥ ͆̄͐ẗ̥́̓ḧ̸͝i̷͚͘s | cursed  | **`zalgo [text]`**  |
| stress  | Creates and sends an image of a user avatar or of a provided picture being stressed by a glass of wine. If no arguments are provided, sender's own avatar is stressed.  |   |  **`stress`**, **`stress [user mention]`**, **`stress [picture url]`**  |
| roll  |  Sends a random number within given numeric span. If no arguments are provided, span of [1, 6] is used. If only one argument is provided, it will be used as a maximum of a span with the minumum of 1.  |   | **`roll`**, **`roll [max]`**, **`roll [min] [max]`**   |
| read  | Sends a writing-related curio for a user to interact with. Results depend on user's reactions and may include written text, images or literally nothing. | book, heckbooks, knowledge  | **`read`**, **`read [action name]`**  |
| reveal  | Adds tentacles to a picture or to a user avatar. If no arguments are provided, sender's own avatar is used.  | tentacles  | **`reveal`**, **`reveal [user mention]`**, **`reveal [picture url]`**  |
| remind  | Reminds a user or a role about important life lesson via text message. |   | **`remind`**, **`remind [user mention]`**, **`remind [role mention]`**  |

### Chat Reactions
Chat reactions occur when a user performs a specific action in a text channel. 
Currently AncestorBotPHP has following reactions to text messages:

- **`[My resolve is tested]` ** or ** `[user mention] resolve is tested` ** or  **`...resolve is tested...`**  — tests one's resolve, with a 25% chance of a virtue. Case insensitve.
- **`[picture or URL in NSFW-named or NSFW-marked channel]`**  — 20% chance to respond with a snarky comment.



### Command Examples

| Command   | Image  |
| ------------ | ------------ |
| zalgo  |  ![zalgo](readme/data/zalgo_example.png "zalgo") |
|  gold |  ![gold](readme/data/gold_example.png "gold") |
|  remind |  ![remind](readme/data/remind_example.png  "remind") |
| stress  |  ![stress](readme/data/stress_example.png "stress") |
|  roll |  ![roll](readme/data/roll_example.png "roll") |
|  spin |   ![spin](readme/data/spin_example.gif "spin")|
|   read (initial action)|  ![read1](readme/data/read_example.png "read1") |
|  read (subsequent action) |   ![read2](readme/data/read_example_2.png "read2")|
|  reveal |   ![reveal](readme/data/reveal_example.png "reveal")|


### Chat Reactions Examples

- **Testing resolve**

![Testing Resolve](readme/data/resolve_example.png "Testing Resolve")


- **Responding to content or links in a NSFW channel**

![NSFW response](readme/data/nsfw_example.png "NSFW response")


## Credits
Darkest Dungeon is a game made by Red Hook Studios.

AncestorBot is a Discord bot made by KolFoxy using [DiscordPHP](https://github.com/discord-php/DiscordPHP "DiscordPHP") and other PHP libraries specified in [Composer file](composer.lock "composer.lock").