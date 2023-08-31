#https://xkln.net/blog/please-stop-using-win32product-to-find-installed-software-alternatives-inside/
$Apps = @();
$Apps += Get-ItemProperty "HKLM:\SOFTWARE\Wow6432Node\Microsoft\Windows\CurrentVersion\Uninstall\*" | where {$_.DisplayName -ne $null } | Format-List DisplayName ; # 32 Bit
$Apps += Get-ItemProperty "HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\Uninstall\*" | where {$_.DisplayName -ne $null } | Format-List DisplayName ;             # 64 Bit
foreach ($App in $Apps) {
   Write-Output $App;
}