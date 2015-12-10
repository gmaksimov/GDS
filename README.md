# GDS
## You need to have constants.txt with variables in web root folder
### Required variables:

`_mysqli_user`

`_mysqli_pass`

`_mysqli_db`

`root_login`

`root_psw`

`current_print_folder`
### Default value
`current_print_folder=print4`
### Example adding variable:
`_mysqli_user=name`

## Database
### Create db
### Don't forget to put these values to constants.txt
`_mysqli_user`

`_mysqli_pass`

`_mysqli_db`
### Export
Use `table.sql` to do it
## Used libraries
### Tcpdf (print3, print4) (recommended)
[Tcpdf](http://sourceforge.net/projects/tcpdf/files/) 6.2.11 and above
Put `tcpdf/` into site folder
### Tfpdf (print2)
[Tfpdf](http://fpdf.org/fr/script/script92.php) 1.24 and above
Put all files inside `print2`
### Tinymce
