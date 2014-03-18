FORK Pentest Suite
================

A multi-platform / multi-vector payload generation and deployment framework for client side attacks. Think Metasploit, but with a web based command & control interface that can track, manage, and distribute payloads to multiple clients seamlessly. 



1.ABOUT FORK

******************************************

The FORK Pentest Suite is a modernized remote access tool employing REST principles and multi platform support. Fork at it's core is an API and admin interface for command and control over a multitude of client side shells such as; chrome/firefox extensions, windows binaries, powershell scripts, javascript, and more. Each of these attack vectors are called, "Prongs". Fork attempts to standardize the control and management of many different Prongs in a single web interface. This means Fork can generate basic payloads for shells in many situations, such as a javascript payload for XSS, a windows binary for desktop, or a chrome extension for android handhelds. All of these payloads will connect back to web server to run user customizeable "Tasks". Tasks employ the REST principle of "Code On Demand" keeping payloads small, but dynamic. Fork can aid in automated pentesting by improving workflow, and centralizing data collection. What's more, is Fork is scalable and highly modular. Once a client has been "Forked" custom user payloads can be injected into the Prong. For example, if the client has been Forked with a Windows binary Prong, the user can inject a Meterpreter payload through the Prong and further pivot his attack into the system. Each Prong is designed to be as minimalist and non intrusive as possible. The prongs act as basic shells on first infection, wherin further code can be injected into these prongs to extend the capabilites on the client.

  
2.WHY FORK?

******************************************

Fork aims to solve the furstrations found in these two common pentesting tool categories; 

Remote Administration:

- "RATs" as they are known are not very modern. Many if not all projects are discontinued and use outdate technolgies and methods.
- They tend to have bloated client side payloads, which most, if not all client functions stored inside. Fork instead strives to keep as much code server side as possible.
- The client side code tends to be highly coupled, not very scalable, and RPC based. Fork is dynamic and modular and adheres to REST principles.
- The "Command & Control" tends to be in the form of a desktop only application. Fork is an easy to install web panel, that can be viewed from and desktop, mobile or tablet device.
- They are often hard to configure and frequently; drop clients, become unresponsive, and set of client anti-viruses. Fork has been carefully designed in order to avoid these common archetectirual and technical design flaws. The Payloads for the prongs will always try to be as memory resident as possible.
- They tend to have only one point of access to a client. With Fork, by leveraging multiple prongs into a target, you ensure there are no weak links to lose your connection. Traditional RAT's rely on mostly executable proccesses to access the client, where Fork provides many different "Prongs" to hook the client, from script based, to browser, to memory injection.

Payload Generation:

- Many application exist to generate shells. Fork is here to bridge the gap in staging these many different paylods, and controling their distribution from a centralized panel. - During a pentest the right payload to send to the client isn't always immidiately known. Certain payloads will fail, some may set of anti virueses or host intrusion systems. The Prongs in Fork are meant to act as a clean, compatabile, and empty backdoor shell that blends in with the clients natural traffic. Afterwords these Prongs can be dynamically reprogrammed, and further payloads can be injected into the sinks of the Prongs. If one fails, or become detected, we can scale back and revert to the original payload. This leaves the guessing game as to which payload to choose quickly when the time comes.
- Many payloads have specific features, like Beef-XSS with it's browser tools, or Meterpreter and it's exploits. The payloads used in for each Prong in Fork have only 3 tasks; stay "Forked", stay stealthy, allow further payload injection.
- Fork makes it easy to manage, update, and distribute many different payloads over a large client base very simply. Advanced reports and statistics allow you to track and tag different versions of your custom payloads, as well as catergorize many clients.

3. WHAT FORK ISN'T

******************************************

Fork is not a; "bot", "worm", "trojan", nor a "RAT". Fork neither steals, or damages data. Fork is merely a tool to help pentesters quickly generate paylods for a variety of different platforms, vectors, and sinks without having to mess around with many other tools at once.

4. WHAT ARE THE DEPENDENCIES?

******************************************

Fork only requires a server with PHP 5.4 and MySQL 5+.

5. HOW DO I INSTALL FORK?

******************************************

If you haven't already, check out the documentation in the "docs" directory, and then read the INSTALL file for installation instructions.
