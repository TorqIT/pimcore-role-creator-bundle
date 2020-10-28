# pimcore-admin-role-creator-bundle

This bundle will use the layout in `\src\pimcore-root\app\config\roles.yml` to create and save roles in the pimcore admin. It will not delete or remove existing roles. In the repo, their is an example roles.yml file, with the layout the command expects.

# COMING SOON

We will soon be implementing `Permissions` and `Workspaces`. Check back soon!

# Installing the package via composer

This bundle is easily installed via composer: `composer require torqit/pimcore-role-creator-bundle`

# Steps to setting up the layout of your folder structure in pimcore admin:
1. Create a `roles.yml` file, with the layout you require.
2. Place the `roles.yml` file you created in your configuration yaml folder like: `\src\pimcore-root\app\config\roles.yml`.
3. Make sure you register the `TorqITRoleCreatorBundle` in your `AppKernel.php` located at `\src\pimcore-root\app\AppKernel.php`. Registering the bundle is as easy as adding a line in the registerBundlesToCollection function, like so: `$collection->addBundle(new \TorqIT\PimcoreRoleCreatorBundle\RoleBundle\TorqITPimcoreRoleCreatorBundle);`
4. Run the bundle, with the command: `./bin/console torq-it-role-creator:generate-roles`
