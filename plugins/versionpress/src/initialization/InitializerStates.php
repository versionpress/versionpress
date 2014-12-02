<?php

class InitializerStates {

    const START = "Starting up";

    const DB_TABLES_CREATED = "Helper database tables created";

    const VPIDS_CREATED = "Entities uniquely identified";

    const REFERENCES_CREATED = "Cross-references created";

    const DB_WORK_DONE = "All database work done";

    const CREATING_GIT_REPOSITORY = "Creating Git repository";

    const VERSIONPRESS_ACTIVATED = "VersionPress put into active state";

    const CREATING_INITIAL_COMMIT = "Creating initialization commit in the repository";

    const FINISHED = "All finished, VersionPress is ready";
}