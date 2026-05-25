#!/usr/bin/env bash
# install-wp-tests.sh
# Instala WordPress test suite en /tmp/wordpress-tests-lib
#
# Uso: bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version]
# Ejemplo: bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

if [ $# -lt 3 ]; then
    echo "Uso: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version]"
    exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}

WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress}

download() {
    if [ $(which curl) ]; then
        curl -s "$1" > "$2"
    elif [ $(which wget) ]; then
        wget -nv -O "$2" "$1"
    fi
}

if [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+\-(beta|RC)[0-9]+$ ]]; then
    WP_BRANCH=${WP_VERSION%\-*}
    WP_TESTS_TAG="branches/$WP_BRANCH"
elif [[ $WP_VERSION =~ ^[0-9]+\.[0-9]+$ ]]; then
    WP_TESTS_TAG="branches/$WP_VERSION"
elif [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0-9]+ ]]; then
    if [[ $WP_VERSION =~ [0-9]+\.[0-9]+\.[0] ]]; then
        WP_TESTS_TAG="tags/${WP_VERSION%.\0}"
    else
        WP_TESTS_TAG="tags/$WP_VERSION"
    fi
elif [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
    WP_TESTS_TAG="trunk"
else
    download http://api.wordpress.org/core/version-check/1.7/ /tmp/wp-latest.json
    LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//');
    if [[ -z "$LATEST_VERSION" ]]; then
        echo "No se pudo determinar la última versión de WordPress"
        exit 1
    fi
    WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

set -ex

install_wp() {
    if [ -d $WP_CORE_DIR ]; then
        return;
    fi

    mkdir -p $WP_CORE_DIR

    if [[ $WP_VERSION == 'nightly' || $WP_VERSION == 'trunk' ]]; then
        mkdir -p /tmp/wordpress-latest
        download https://wordpress.org/nightly-builds/wordpress-latest.zip /tmp/wordpress-latest.zip
        unzip -q /tmp/wordpress-latest.zip -d /tmp/wordpress-latest/
        mv /tmp/wordpress-latest/wordpress/* $WP_CORE_DIR
    else
        if [ $WP_VERSION == 'latest' ]; then
            local ARCHIVE_NAME='latest'
        else
            local ARCHIVE_NAME="wordpress-$WP_VERSION"
        fi
        download https://wordpress.org/${ARCHIVE_NAME}.tar.gz /tmp/wordpress.tar.gz
        tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR
    fi
}

install_test_suite() {
    if [ -d $WP_TESTS_DIR/includes ]; then
        return;
    fi

    mkdir -p $WP_TESTS_DIR

    SVN_URL="https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/"
    svn co --quiet $SVN_URL $WP_TESTS_DIR/includes 2>/dev/null
    if [ $? -ne 0 ]; then
        download "https://raw.github.com/wordpress/wordpress-develop/${WP_TESTS_TAG}/tests/phpunit/includes/bootstrap.php" "$WP_TESTS_DIR/includes/bootstrap.php"
        download "https://raw.github.com/wordpress/wordpress-develop/${WP_TESTS_TAG}/tests/phpunit/includes/functions.php" "$WP_TESTS_DIR/includes/functions.php"
    fi
}

install_db() {
    if [ "${SKIP_DB_CREATE:-false}" = "true" ]; then
        return
    fi

    PARTS=(${DB_HOST//\// })
    HOSTNAME_ONLY=${PARTS[0]};
    SOCKET_OR_PORT=${PARTS[1]};
    EXTRA=""

    if ! [ -z $SOCKET_OR_PORT ] ; then
        if [ $(echo $SOCKET_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
            EXTRA=" --host=$HOSTNAME_ONLY --port=$SOCKET_OR_PORT --protocol=tcp"
        else
            EXTRA=" --socket=$SOCKET_OR_PORT"
        fi
    elif ! [ -z $HOSTNAME_ONLY ] ; then
        EXTRA=" --host=$HOSTNAME_ONLY --protocol=tcp"
    fi

    mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA 2>/dev/null || true
}

configure_wp() {
    if [ ! -f "$WP_TESTS_DIR/wp-tests-config.php" ]; then
        download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$WP_TESTS_DIR/wp-tests-config.php"
        if [[ "$(uname)" == "Darwin" ]]; then
            SED_INPLACE=( sed -i '' )
        else
            SED_INPLACE=( sed -i )
        fi
        "${SED_INPLACE[@]}" "s/youremptytestdbnamehere/$DB_NAME/" "$WP_TESTS_DIR/wp-tests-config.php"
        "${SED_INPLACE[@]}" "s/yourusernamehere/$DB_USER/" "$WP_TESTS_DIR/wp-tests-config.php"
        "${SED_INPLACE[@]}" "s/yourpasswordhere/$DB_PASS/" "$WP_TESTS_DIR/wp-tests-config.php"
        "${SED_INPLACE[@]}" "s|localhost|${DB_HOST}|" "$WP_TESTS_DIR/wp-tests-config.php"
        "${SED_INPLACE[@]}" "s|dirname( __FILE__ ) . '/src/'|'${WP_CORE_DIR}/'|" "$WP_TESTS_DIR/wp-tests-config.php"
    fi

    mkdir -p tests
    cp "$WP_TESTS_DIR/wp-tests-config.php" "tests/wp-tests-config.php"
}

install_wp
install_test_suite
install_db
configure_wp

echo ""
echo "WordPress test suite instalada en $WP_TESTS_DIR"
echo "Base de datos '$DB_NAME' lista (o ya existia)"
echo ""
echo "Ahora puedes correr: vendor/bin/phpunit"
