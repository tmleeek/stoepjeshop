Commerce Bug Installation Guide
==================================================

1. Copy/Upload the contents of the "app" folder to the corresponding "app" folder of your Magento system

2. Copy/Upload the contents of the "skin" folder to the corresponding "skin" folder of your Magento system

3. Ensure the uploaded files and folders have *nix permissions, ownership and groups identical to the rest of your Magento files and folder

4. Clear the Magento Cache.  This may be done under System -&gt; Cache Management

5. Log out of the Magento Admin Application and clear you sessions.  Also, if you're using compilation, you'll need to re-compile your classes.

6. Configure Commerce Bug (update notifications, output, etc.) at System -&gt; Advanced -&gt; Commerce Bug

7. If you've installed Magento in a sub-directory of your site, be sure to sure to set the **Commerce Bug** skin path in System -&gt; Advanced -&gt; Commerce Bug.  If you don't do this, you'll see the Debug menu, but clicking it won't do anything.

8. In the Magento Admin, make sure [System -> Configuration -> Developer -> Template Settings -> Allow Symlinks] is set to **Yes**
