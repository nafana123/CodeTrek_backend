FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive
ENV TZ=Etc/UTC

RUN echo "Установка утилит..." && \
    apt-get update && apt-get install -y \
    curl wget git unzip vim nano tzdata && \
    apt-get clean && echo "Утилиты установлены." \

RUN echo "Установка Docker CLI..." && \
    apt-get update && apt-get install -y \
    ca-certificates gnupg lsb-release && \
    mkdir -m 0755 -p /etc/apt/keyrings && \
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
    gpg --dearmor -o /etc/apt/keyrings/docker.gpg && \
    echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] \
    https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | \
    tee /etc/apt/sources.list.d/docker.list > /dev/null && \
    apt-get update && \
    apt-get install -y docker-ce-cli && \
    apt-get clean && \
    echo "Docker CLI установлен."

RUN echo "Установка PHP..." && \
    apt-get update && apt-get install -y \
    php php-cli php-mbstring php-xml php-curl php-intl php-zip php-sqlite3 php-mysql  && \
    apt-get clean && echo "PHP установлен."

RUN echo "Установка Node.js и NPM..." && \
    apt-get update && apt-get install -y \
    nodejs npm && \
    apt-get clean && echo "Node.js и NPM установлены."

RUN echo "Установка Python и pip..." && \
    apt-get update && apt-get install -y \
    python3 python3-pip && \
    apt-get clean && echo "Python и pip установлены."

RUN echo "Установка Go..." && \
    apt-get update && apt-get install -y \
    golang && \
    apt-get clean && echo "Go установлен."

RUN echo "Установка OpenJDK..." && \
    apt-get update && apt-get install -y \
    openjdk-17-jdk && \
    apt-get clean && echo "OpenJDK установлен."

RUN echo "Установка GCC и G++..." && \
    apt-get update && apt-get install -y \
    gcc g++ && \
    apt-get clean && echo "GCC и G++ установлены."

RUN echo "Установка .NET SDK..." && \
    wget https://packages.microsoft.com/config/ubuntu/22.04/packages-microsoft-prod.deb -O packages-microsoft-prod.deb && \
    dpkg -i packages-microsoft-prod.deb && \
    apt-get update && apt-get install -y dotnet-sdk-8.0 && \
    apt-get clean && echo ".NET SDK установлен."

WORKDIR /app

COPY . /app

RUN chmod -R 755 /app

CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]

EXPOSE 8000
