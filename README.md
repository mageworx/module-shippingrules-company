# MageWorx Shipping Rules Company Conditions (B2B) Extension for Magento 2

## Upload the extension

### Upload via Composer

**1) Using packagist**
- Execute the following command:  
```
composer require mageworx/module-shippingrules-company
```

**2) Using local artifact repository**
1. Log into Magento server (or switch to) as a user who has permissions to write to the Magento file system.
2. Create a folder anywhere on your server (preferably not in the Magento install dir). When done, upload all extension zip packages in there.
3. To use the folder created above as a packaging repository, add the run composer command:
    ```
    composer config repositories.mageworx artifact {YOUR/ABSOLUTE/PATH/TO/EXTENSIONS/DIRECTORY}
    ```
    For example:
    ```
    composer config repositories.mageworx artifact /Users/mageworxuser/magento_extensions/mageworx/zip
    ```

    This command add to your composer.json file this lines:

    ```
    "mageworx": {
        "type": "artifact",
        "url": "/Users/mageworxuser/magento_extensions/mageworx/zip"
    }
    ```

4. Install the extension with Composer:
    ```
    composer require mageworx/module-shippingrules
    ```

### Upload by copying code

1. Log into Magento server (or switch to) as a user who has permissions to write to the Magento file system.
2. Download this module and upload it to the `app/code/MageWorx/ShippingRulesCompany` directory *(create "ShippingRulesCompany" first if missing)*


## Enable the extension

1. Log in to the Magento server as, or switch to, a user who has permissions to write to the Magento file system.
2. Go to your Magento install dir:
```
cd <your Magento install dir> 
```

3. And finally, update the database and autogenerated files:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Features

### Adds new conditions for the Shipping Suite rules

| Condition                | Description                                                                                        |
|--------------------------|----------------------------------------------------------------------------------------------------|
| *Company ID*             | The company to which the current buyer is assigned                                                 |
| *Customer ID*            | ID of the current buyer                                                                            |
| *Job Title*              | Job title of the current buyer                                                                     |
| *Current Company Status* | Company status of the company to which the current buyer is assigned                               |
| *Telephone*              | Phone number of the company to which the current buyer is assigned                                 |
| *Company Name*           | Name of the company to which the current buyer is assigned                                         |
| *Legal Name*             | Legal Name of the company to which the current buyer is assigned                                   |
| *Company email*          | Email address of the company to which the current buyer is assigned                                |
| *VAT Tax ID*             | VAT Tax ID of the company to which the current buyer is assigned                                   |
| *Reseller ID*            | Reseller ID of the company to which the current buyer is assigned                                  |
| *Street*                 | Street part of the address of the company to which the current buyer is assigned                   |
| *Country Id*             | Country part of the address of the company to which the current buyer is assigned                  |
| *Region Id*              | Region part of the address of the company to which the current buyer is assigned                   |
| *Region*                 | Region (as a plain text) part of the address of the company to which the current buyer is assigned |
| *Postcode*               | Postcode part of the address of the company to which the current buyer is assigned                 |


Here's how the new attributes look like in the condition section of the shipping rules:

![Preview in conditions 1](https://github.com/mageworx/module-shippingrules-company/raw/update_readme/images/example_1.png)

![Preview in conditions 2](https://github.com/mageworx/module-shippingrules-company/raw/update_readme/images/example_2.png)

> Important note: This feature is available only if you have Magento B2B modules installed on your system.
