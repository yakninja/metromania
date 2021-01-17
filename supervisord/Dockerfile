FROM yiisoftware/yii2-php:7.4-apache

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install -y locales supervisor

# Set the locale
RUN sed -i -e 's/# en_US.UTF-8 UTF-8/en_US.UTF-8 UTF-8/' /etc/locale.gen && \
    dpkg-reconfigure --frontend=noninteractive locales && \
    update-locale LANG=en_US.UTF-8
ENV LANG en_US.UTF-8

# supervisord config
RUN mkdir /etc/supervisord.d && \
    mkdir -p /var/log/supervisor
COPY supervisord/supervisord.conf /etc/supervisord.conf

ENTRYPOINT ["supervisord", "--nodaemon", "--configuration", "/etc/supervisord.conf"]
