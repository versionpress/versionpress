# About EAP

VersionPress is a young and ambitious project and until it matures a bit, we distribute it through the *Early Access Program* (EAP). The program can be joined at [versionpress.net](http://versionpress.net).


## Overview

EAP:

 1. Explicitly marks the software as young, unfinished and limited in scenarios it supports (for example, some 3<sup>rd</sup> party plugins might not be supported, shared hosting might be an issue, etc.)
 2. Still provides access to anyone interested
 3. Helps fund the development

View it as a paid beta which sounds turned on its head but has actually worked well for us and our users. We expect EAP to be in place til roughly Q1/Q2 2016.

**Please [join](http://versionpress.net/#get)** the EAP if the idea of Git + WordPress + database versioning excites you, want to support the development, be one of the first to play with VersionPress and don't mind the items not yet completed on the [roadmap](../release-notes/roadmap).

**Do not join** the EAP if you expect VersionPress to *just work* and/or want to use it on a mission-critical website. Also, please note that the payment is for the membership in the program, not for the software itself (VersionPress is 100% GPL).  


## EAP recommendations

If you're going to use EAP versions of VersionPress (we do and some of our users do, too), please take the following advice seriously:   

 - **Ideally, use VersionPress for testing / dev purposes only**. Local, throw-away sites and workflows are ideal.

 - **If you're going to run VersionPress on a live site, <span style="color:red;">keep backup at all times</span>**. We really mean this. VersionPress manipulates the database during revert operations and for EAP releases, backups are mandatory.

 - **Controlled hosting** is recommended. VersionPress requires Git on the server and `proc_open()` enabled which only some hosts allow (see [hosting](../integrations/hosts)).

 - **Be familiar with WordPress and Git**. While the big promise of VersionPress is that it will be usable versioning solution for everyone, both technical and nontechnical people, at this stage familiarity with Git and WordPress will help.

 - **Approach VersionPress with care**, especially when it comes to complex third-party plugins like e-commerce solutions, "page builders" etc. This is explained on the [external plugins](../feature-focus/external-plugins) page in more detail.

You can tell whether you are using an EAP release of VersionPress from the top admin area where there will be a clear EAP warning.

