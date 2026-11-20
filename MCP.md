# Using DeepskyLog with Claude (MCP)

DeepskyLog has an MCP server at `https://www.deepskylog.org/mcp`. When you
connect an AI assistant such as Claude to it, you can ask about your logbook,
your equipment and the coming nights in ordinary language. Claude reads the
answers from DeepskyLog itself, so they are your data and DeepskyLog's
calculations, not guesses.

Claude acts as you, with the rights of a personal access token you make
yourself. It sees your own observations, equipment, locations, lists and
goals, and everything DeepskyLog shows publicly. It never sees other
observers' private data, and it cannot change or delete anything. The
exceptions are creating an observing list and changing the description of
one of your own lists, and only with a token that has the `write`
permission.

## 1. Make an access token

1. Log in to DeepskyLog and open **API tokens** in the user menu
   (`/user/api-tokens`).
2. Give the token a name, for instance `Claude`.
3. Leave **read** ticked. Also tick **write** if Claude may create observing
   lists and change their descriptions for you.
4. Copy the token right away. DeepskyLog shows it only once.

Treat the token like a password. If it leaks, delete it on the same page and
make a new one.

## 2. Connect Claude

### Claude Code (terminal, VS Code, JetBrains)

```bash
claude mcp add --transport http --scope user deepskylog \
  https://www.deepskylog.org/mcp \
  --header "Authorization: Bearer YOUR_TOKEN"
```

`--scope user` makes DeepskyLog available in every project. Leave it out to
add it to the current project only. Check the connection with `/mcp` inside
Claude Code: `deepskylog` should be listed as connected, with 12 tools, or 13
with a write token.

### Claude Desktop

Claude Desktop's own connectors need OAuth, which DeepskyLog does not offer
yet. Connect through the small `mcp-remote` bridge instead, which needs
Node.js. Add the `mcpServers` part below to `claude_desktop_config.json`,
next to whatever the file already holds. You can open the file in two ways:

- In Claude Desktop, choose **Settings → Developer → Edit config**. The
  Developer section only appears after you turn on developer mode in the
  app menu (☰ at the top left): **Help → Enable Developer Mode**.
- Or open the file directly in a text editor:
  - Linux: `~/.config/Claude/claude_desktop_config.json`
  - macOS: `~/Library/Application Support/Claude/claude_desktop_config.json`
  - Windows: `%APPDATA%\Claude\claude_desktop_config.json`

In an organization account (Team or Enterprise), the administrators can turn
off local MCP servers. If DeepskyLog doesn't appear after a restart, ask them.

```json
{
  "mcpServers": {
    "deepskylog": {
      "command": "npx",
      "args": [
        "-y", "mcp-remote",
        "https://www.deepskylog.org/mcp",
        "--header", "Authorization: Bearer ${DEEPSKYLOG_TOKEN}"
      ],
      "env": { "DEEPSKYLOG_TOKEN": "YOUR_TOKEN" }
    }
  }
}
```

Restart Claude Desktop. DeepskyLog then appears under the tools icon of the
chat box.

### claude.ai (web) and the mobile apps

These need OAuth, which DeepskyLog does not offer yet, so they can't connect
for now.

## 3. What you can ask

You don't need to name the tools. Just ask, and Claude picks the tools and
combines them. The examples below are grouped by what they use. The tool
names in brackets are only for reference.

### Your equipment and sites

- "Which telescopes and eyepieces do I have in DeepskyLog, and which one is my
  standard telescope?" *(get-my-profile)*
- "What magnifications do I get with my 10-inch and my eyepieces?"
- "What is in my travel kit, and which magnifications and fields does it
  give?" *(list-my-instrument-sets: your instrument sets, and for one set
  the magnification, exit pupil and true field of each telescope with each
  eyepiece and lens of it)*
- "Which of my locations has the darkest sky?"

Claude usually starts with your profile. The other questions use your
standard telescope and location. For a single object you can name another
instrument ("with my binoculars"), and for Tonight's best and the forecast
another of your locations ("from the Ardennes site"). Tonight's best always
uses your standard telescope.

### Objects

- "Find the Veil Nebula in DeepskyLog." *(search-objects)*
- "Tell me about NGC 7331. Can I see it with my telescope from home, and at
  what magnification?" *(get-object: the contrast reserve and the best
  magnification)*
- "Can my 8-inch split STF 2382?" *(get-object: for a double star, whether
  your telescope splits it)*
- "What does Dreyer's description of NGC 891 mean in plain words?"
- "Observers who saw Stephan's Quintet, what else did they see?"

Names can be written in any common spelling: `M31`, `M 31`, `Andromeda
Galaxy`, `Sigma 2816`, `NGC891`.

### Tonight and the coming nights

- "What should I observe tonight?" *(get-tonights-best)*
- "Give me 15 objects for the second half of the night, only ones I haven't
  seen, from the Messier and Caldwell catalogs."
- "Will it be clear this week at my standard location? When should I put my
  Dobsonian outside to cool down?" *(get-night-forecast: clouds, Moon, seeing,
  dew and cool-down time)*
- "Show me the forecast hour by hour for Saturday night."
- "Which night this week is best for planets?" (uses the seeing forecast)

### Your logbook

- "What did I observe last month?" *(list-my-observations)*
- "Show me all my observations of M 51 with the full descriptions."
  *(get-observation for the full text)*
- "Which objects did I look for but not see in 2025?"
- "List the observations I made a drawing of this year."
- "How many different deep-sky objects have I seen, and in which
  constellations did I observe most?" *(get-my-statistics)*
- "Compare my observing year 2025 with 2024."

### Catalogs, lists and goals

- "How far am I with the Herschel 400? Which ones do I still need?"
  *(get-catalog-progress)*
- "Which Messier objects have I seen but never drawn?"
- "Show my observing lists." *(list-my-observing-lists)*
- "Only show my public lists which don't have a description."
  *(list-my-observing-lists, with own_only, public and has_description)*
- "Which objects on my list 'Autumn galaxies' haven't I seen yet?"
  *(get-observing-list)*
- "How are my observing goals going? Will I make the Arp goal before the end
  of the year?" *(list-my-goals)*

### Creating an observing list (write token only)

- "Make tonight's best into an observing list called 'Friday night'."
- "Put the first 20 Herschel 400 objects I still need into a private list."
- "Create a list with M 81, M 82, NGC 3077 and NGC 2976, in that order."

Claude asks before it creates a list. A new list is private unless you ask
for a public one. Names DeepskyLog does not know are reported and left out.
The answer has the link to the new list, where you can plan its times, take
it to the eyepiece or export it.

### Changing the description of a list (write token only)

- "Write a short description for each of my public lists that has none."
- "Change the description of 'Ultra Thin Galaxies' to: Edge-on galaxies
  thinner than 1:10, best in a 12-inch or larger."

Claude shows you the new text and asks before it changes a list
*(update-observing-list-description)*. Only your own lists can be changed;
an empty description removes it.

## 4. Questions that combine tools

Claude is most useful when it chains several tools together:

- "Which clear night this week is best, and what should I observe then from
  my darkest site?"
  Uses the forecast for each of your sites, then Tonight's best for the
  chosen night and site.
- "Plan a galaxy night: H400 galaxies I haven't seen that my 12-inch shows
  well, in a list in observing order."
  Uses catalog progress, then the objects one by one, then creates the list.
- "Look at my observations of the last three sessions and suggest what to
  look at next, similar objects I haven't seen yet."
  Uses your observations and the objects that other observers who saw them
  also saw.
- "Write a short report of my observing year for my club newsletter."
  Uses your statistics and your best observations.

## 5. Good to know

- **Dates** are the evening of the night (`2026-11-21` is the night from
  Saturday to Sunday). **Times** are local at the location.
- **Contrast reserve**: below −0.2 the object is not visible, −0.2 to 0.1 is
  questionable, 0.1 to 0.35 difficult, 0.35 to 0.5 quite difficult, 0.5 to 1
  easy, and above 1 very easy. Claude knows this scale and explains it.
- Answers contain links to the DeepskyLog pages. Ask Claude for the link when
  you want to read more or edit something.
- Claude can't log observations or change your data. Do that on the site.
- There is a limit of 60 requests a minute and 600 an hour. A long question
  can use a dozen requests.
- To stop Claude's access, delete the token on `/user/api-tokens`.
