import json
import xml.etree.ElementTree as ET
import os
import sys

#JSON
with open("src/hipay_enterprise/composer.json", "r") as read_file:
    data = json.load(read_file)

data["version"] = sys.argv[1]

with open("src/hipay_enterprise/composer.json", "w") as write_file:
    json.dump(data, write_file, indent=4)

read_file.close()
write_file.close()

#XML
#tree = ET.parse("src/hipay_enterprise/config_fr.xml")
#root = tree.getroot()

#for version in root.iter("version"):
#    version.text = sys.argv[1]

#tree.write("src/hipay_enterprise/config_fr.xml")

#PHP
read_file = open("src/hipay_enterprise/hipay_enterprise.php", "r")

write_file = open("src/hipay_enterprise/hipay_enterprise.tmp.php", "w")

for line in read_file:
    
    if "$this->version = " in line:
        write_file.write("        $this->version = '" + sys.argv[1] + "';\r");
    else:
        write_file.write(line)

read_file.close()
write_file.close()

os.remove("src/hipay_enterprise/hipay_enterprise.php")
os.rename("src/hipay_enterprise/hipay_enterprise.tmp.php", "src/hipay_enterprise/hipay_enterprise.php")
