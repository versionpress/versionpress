<?php

class InitializerStates {

    /**
     * Initial state, right at the beginning of the process
     */
    const START = "Starting activation";

    const DB_TABLES_CREATED = "Database tables created";

    const VPIDS_CREATED = "All identifiers created";

    const REFERENCES_CREATED = "All references created";

    const DB_WORK_DONE = "Work with database done";

    const CREATING_GIT_REPOSITORY = "Creating Git repository";

    const VERSIONPRESS_ACTIVATED = "VersionPress activated";

    const CREATING_INITIAL_COMMIT = "Creating initial commit";


    /**
     * Last state
     */
    const FINISHED = "All finished, VersionPress fully activated";
}