# CYMIS Database Backups

### Purpose
This directory stores automated SQL snapshots of the **Container Yard Management System (CYMIS)** production database (`cyms_prod`). It exists to guarantee that no operational, financial, or container yard data is permanently lost.

### How It Works
Two Docker sidecar containers work together:

1. **`cyms_db_backup`**: Dumps the full database nightly and compresses it into this folder.
2. **`mega_sync`**: Watches this folder every 60 seconds and uploads any new file to **MEGA.nz**.

### Backup Configuration
| Setting         | Value                         |
|-----------------|-------------------------------|
| Database        | `cyms_prod`                   |
| Schedule        | Daily at **02:00 AM**         |
| Retention       | Last **30 days** (rolling)    |
| Format          | `.sql.gz` (GZIP Level 9)      |
| Off-site Target | MEGA.nz `/backups/cyms_yard`  |

### Restoring a Backup
```bash
# 1. Decompress the file
gunzip 202604010200.cyms_prod.sql.gz

# 2. Restore into the database
mysql -u password -p cyms_prod < 202604010200.cyms_prod.sql
```

### Security Notice
> The `.sql.gz` files are excluded from Git via `.gitignore`.
> They contain sensitive financial and client data. Never commit them to the repository.
> Only `README.md` and `index.html` are tracked by Git.
