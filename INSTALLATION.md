INSTALLATION STEPS
==================

1. Extract the /src/Fork directory onto your server. It's advised
   not to store it directly in your document root. Instead store it in
   a non-web-accessible directory or a directory with privileges that don't allow
   public visibility.

2. Place the contents of the /public directory into your document root, or wherever
   the main index of your site should be served such as www-data, www, public_html etc...
   Make sure to apply proper chmod to the index.

3. Chmod the client-mounts that is located in the public folder that you just copied
   with 700 permissions, or create an htaccess to allow public reading of the directory.
   The client-mounts directory should only be read by the internal server. This directory
   can also be placed outside of the document root if you so choose.

4. Edit the Configuration.php file located in the Fork\Core directory. Make sure all values
   especially the 'Environment' settings match those of your servers.

5. Goto 'mysite.com/forkdirectory/install' where forkdirectory is the location of forks
   index.php. Follow the instructions provided.


   The following additional settings are recommended.
   ==================================================

   SET IN PHP.INI
   - 'post_max_size' = 10-15M is preferred. The more the better
   - 'memory_limit'  = Depending on your host, you might not be able to increase this.
                       but anything over 128M is preferred. It's not advised to use less
                       than 64M.
   - 'upload_tmp_dir'= Make sure the default is accessible by the script, and if not
                       set your own custom tmp directory.