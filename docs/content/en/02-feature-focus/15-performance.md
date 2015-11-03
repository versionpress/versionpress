# Performance Considerations #

VersionPress generally consumes server resources only after some change has occurred on the web site while for read-only requests, it adds hardly any overhead at all (WordPress fetches the data like if there was no VersionPress).

If you have a site that is very write-heavy then VersionPress might not be a good fit but for most websites out there where 99% of web requests are read-only, VersionPress is not a performance bottleneck. There are a couple of operations that are slow, however:

## Slow operations

### Reverts

If there is one category of operations that is generally slow, it's reverts. It takes some time to Git itself to do the revert on the file-system level and VersionPress then spends some more time to reflect those changes into a database. We have done some significant performance optimizations in this area already so it's not too bad but still, reverts are generally slower than all other actions VersionPress does.

### Initialization

The other slow operation, that you'll fortunately encounter very rarely, ideally once, is the initial activation, or, initialization. When VersionPress first creates a mirror of the site in the Git repository, it basically needs to go through the whole database, extract data from it and commit it to the repository together with all the files. This can take many seconds or even minutes for large sites. The pre-activation screen will try to make an estimate of how many entities are there in the database and whether PHP time limit should allow it to run but if you encounter any issues, try increasing the timeout or allocate more hardware resources to the site.   