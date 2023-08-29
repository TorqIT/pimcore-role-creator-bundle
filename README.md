# pimcore-admin-role-creator-bundle

## Getting started

1. This bundle is easily installed via composer: `composer require torqit/pimcore-role-creator-bundle`
2. In your config folder, add a `roles.yml` file. Instructions on how to set up your roles is given below in the Roles Setup section.
4. Make sure you register the `RoleCreatorBundle` in your `AppKernel.php` located at `\src\pimcore-root\app\AppKernel.php`. Registering the bundle is as easy as adding a line in the registerBundlesToCollection function, like so: `$collection->addBundle(new \TorqIT\RoleCreatorBundle\RoleCreatorBundle);`
5. Run the bundle, with the command: `./bin/console torq:generate-roles`

## Role Setup

For this example, let's say we want to add `Manager` and `Employee` roles to our app. In your config folder, add a `roles.yml` file with the following layout:

```yaml
system_roles:
  Manager:
  Employee:
```

This will create `Manager` and `Employee` roles, both with no permissions, workspaces or allowed classes.

### Basic Permissions

Using the permissions specified in the `user_permission_definitions` table, you can specify basic permissions per role using one of three variables:
- `included_permissions: []` An array of allowed permissions
- `excluded_permissions: []` Include all permissions on a role _except_ for the ones specified
- `all_permissions:` Include all permissions

So for example, if we wanted our manager to have full access to the app, but only allow users to see documents and assets, we would configure it like so:

```yaml
system_roles:
  Manager:
    all_permissions: true
  Employee:
    included_permissions: ["documents", "assets"]
```

### Workspaces

You can specify data object, asset and document workspaces using the following structure per role.

```yaml
workspaces:
  data_objects:
    /folderName: []
  assets:
    /folderName: []
  documents:
    /folderName: []
```

Where `folderName` is the full path to the folder for that workspace. Each workspace array can be populated with the following currently supported permissions:
- `list`
- `view`
- `save` (Documents/Data Objects Only)
- `publish`
- `unpublish` (Documents/Data Objects Only)
- `delete`
- `rename`
- `create`
- `settings`
- `versions`
- `properties`

> _Note:_ in order to make the entire structure available, you can supply `/` as the folder, which will make a workspace at the root.

Going back to our example, if we wanted to make it so that the `Employee` role can only operate in the `articles` folders for documents and assets, we might set up our config this way:

```yaml
system_roles:
  Manager:
    # Manager Permissions
  Employee:
    workspaces:
      data_objects:
        /articles: ["list", "view", "create", "save", "publish"]
      assets:
        /articles: ["list", "view"]
```

Alternatively, you can pass `true` to a workspace, which will enable all of the permissions

```yaml
 ...
    workspaces:
      data_objects:
        /articles: true
```

### Allowed Document Types & Classes

You can specify the allowed document types and classes per role using the following structure:

```yaml
allowed_types:
  classes: ["MyClassName"]
  document_types: ["Document Name"]
```

Where the both values accept the class/document type **name** (and not the class/doc type _ID_). For example, if we wanted to make it so that the `Employee` role could only create `Article`'s, we would simply specify the following:

```yaml
system_roles:
  Manager:
    # Manager Permissions
  Employee:
    allowed_types:
      document_types: ["Article"]
```

> **Note:** The default behavior for pimcore is that if no allowed class/doc types are specified, then _all_ classes and doc types are allowed. If you need to restrict all creation, you may need to configure it at the workspace level.
