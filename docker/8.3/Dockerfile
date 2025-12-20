FROM php:8.3.24-cli-bullseye

RUN apt-get update && \
    apt-get install -y libzip-dev wget git libssh2-1

ARG NEW_RELIC_AGENT_VERSION
ARG NEW_RELIC_DAEMON_ADDRESS

# Install the latest New Relic PHP Agent
RUN cd /tmp \
    # Discover libc provider
    && export NR_INSTALL_PLATFORM=$(ldd --version 2>&1 | grep -q musl && echo "linux-musl" || echo "linux") \
    # Download the discovered version:
    && curl -o newrelic-php-agent.tar.gz https://download.newrelic.com/php_agent/archive/${NEW_RELIC_AGENT_VERSION}/newrelic-php5-${NEW_RELIC_AGENT_VERSION}-${NR_INSTALL_PLATFORM}.tar.gz \
    # Install the downloaded agent:
    && tar xzf newrelic-php-agent.tar.gz \
    && NR_INSTALL_USE_CP_NOT_LN=1 NR_INSTALL_SILENT=0 ./*/newrelic-install install \
    # Daemon address:
    && sed -ie "s/[ ;]*newrelic.daemon.address[[:space:]]=.*/newrelic.daemon.address=${NEW_RELIC_DAEMON_ADDRESS}/" $(php-config --ini-dir)/newrelic.ini \
    # Cleanup temporary files:
    && rm newrelic-php-agent.tar.gz && rm -rf newrelic-php5-*-linux

# Install PHP pecl extensions
RUN pecl install zip && \
    pecl install xdebug

# Enable extensions
RUN docker-php-ext-enable zip xdebug

# Install composer
ADD ./composer-installer.sh /usr/bin/composer-installer.sh
RUN chmod 0775 /usr/bin/composer-installer.sh && \
    /usr/bin/composer-installer.sh && \
    rm -rf /usr/bin/composer-installer.sh && \
    mv composer.phar /usr/bin/composer && \
    chmod 0775 /usr/bin/composer

ARG OWNER_USER
ARG OWNER_USER_ID

# Add owner user if not exists
RUN /bin/bash -c 'useradd -u ${OWNER_USER_ID} ${OWNER_USER}'

# Init dev env
RUN mkdir -p /opt/phpstorm-coverage /home/${OWNER_USER} && \
    chown ${OWNER_USER}:${OWNER_USER} /opt/phpstorm-coverage /home/${OWNER_USER}

# Change user
USER ${OWNER_USER}
