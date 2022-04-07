import json
import xml.etree.ElementTree as ET

#JSON
with open("composer.json", "r") as read_file:
    data = json.load(read_file)

data["version"] = "2.15.2"

with open("composer.json", "w") as write_file:
    json.dump(data, write_file, indent=4)

#XML
tree = ET.parse("config_fr.xml")
root = tree.getroot()

for version in root.iter("version"):
    version.text = "2.15.2"

tree.write("config_fr.xml")
