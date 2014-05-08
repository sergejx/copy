#!/usr/bin/env python3

import os
import sys
import re
import collections
from datetime import datetime
import yaml


LONG_COMMENT = re.compile(r'<span>(.+)</span>(.+)')
SHORT_COMMENT = re.compile(r'<span>(.+)</span>')


# Helper functions. Thanks to stackoverflow :-)
def natural_sort(l):
    convert = lambda text: int(text) if text.isdigit() else text.lower()
    alphanum_key = lambda key: [ convert(c) for c in re.split('([0-9]+)', key) ]
    return sorted(l, key = alphanum_key)


def represent_ordereddict(dumper, data):
    value = []
    for item_key, item_value in data.items():
        node_key = dumper.represent_data(item_key)
        node_value = dumper.represent_data(item_value)
        value.append((node_key, node_value))
    return yaml.nodes.MappingNode(u'tag:yaml.org,2002:map', value)

yaml.add_representer(collections.OrderedDict, represent_ordereddict)


# Reading old gallery info and photo comments
def read_info(path):
    info = collections.OrderedDict()
    with open(os.path.join(path, "info.txt")) as f:
        for line in f:
            key, value = line.split("|")
            info[key] = value.strip()
            if key == 'date':
                info[key] = datetime.strptime(info[key], '%Y-%m-%d').date()
    return info


def read_comments(path):
    comments = collections.OrderedDict()
    for comment_file in natural_sort(os.listdir(os.path.join(path, "comments"))):
        number = comment_file.split(".")[0]
        img_name = "img-" + number + ".jpg"
        with open(os.path.join(path, "comments", comment_file)) as f:
            comment = f.read()
        comment = comment.strip()
        comment = LONG_COMMENT.sub(r'\1 |\2', comment)
        comment = SHORT_COMMENT.sub(r'\1', comment)
        comments[img_name] = comment
    return comments


# New format is based on YAML
def write_info_yaml(path, info, comments):
    with open(os.path.join(path, "info.yaml"), 'w') as configfile:
        yaml.dump(info, configfile, explicit_start=True, allow_unicode=True,
                  width=400)
        yaml.dump(comments, configfile, explicit_start=True, allow_unicode=True,
                  width=400)


if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Usage new_info.py <GALLERIES_BASE_DIR>")
        sys.exit()
    base_path = sys.argv[1]

    for gallery in os.listdir(base_path):
        print("Converting '{}' ... ".format(gallery), end="")
        path = os.path.join(base_path, gallery)
        info = read_info(path)
        comments = read_comments(path)
        write_info_yaml(path, info, comments)
        print("OK")
