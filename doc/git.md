# GIT

## Branches

The **main** branch is the one used to deliver the application on **production**.

The **preprod** branch is the one used to deliver the application on **preproduction**.

Any other branch must be prefixed with the jira ticket number. Example: **spar-414_invoice-pdf**.

## Workflow

When you create a new branch, do it always from **main**.

When you need to rebase your branch, do it always from **main**.

When you have finished your development, you have to:

* Rebase the **main** branch into your branch (only if needed and if your branch has never been merged).
* Create a bitbucket **Pull Request** from your branch to **preprod**.

When this **Pull Request** is validated, it will :

* Merge your branch into **preprod**.
* Deliver the **preprod** branch into the **preproduction** environment.
* Do not delete your branch at this time.

When your development has been validated on the **preproduction**, you have to:

* Merge the **main** branch into your branch (only if needed).
* Create a bitbucket **Pull Request** from your branch to **main**.

When this **Pull Request** is validated, it will :

* Merge your branch into **main**.
* Deliver the **main** branch into the **production** environment.
* You **must** delete your branch at this time.
 